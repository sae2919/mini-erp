<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    // ─── Purchase (Stock IN) ──────────────────────────────────────────────────

    public function createPurchase(array $data): Purchase
    {
        return DB::transaction(function () use ($data) {

            $totalAmount = collect($data['items'])->sum(
                fn($item) => $item['quantity'] * $item['cost_price']
            );

            $purchase = Purchase::create([
                'supplier_id'   => $data['supplier_id'],
                'reference'     => Purchase::generateReference(),
                'purchase_date' => $data['purchase_date'],
                'total_amount'  => $totalAmount,
                'notes'         => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id'  => $item['product_id'],
                    'quantity'    => $item['quantity'],
                    'cost_price'  => $item['cost_price'],
                ]);

                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $product->increment('stock_quantity', $item['quantity']);
            }

            // ── Audit log ──────────────────────────────────────────────────
            ActivityLogger::created(
                $purchase,
                "Purchase {$purchase->reference} recorded — ₹{$totalAmount}, stock updated for "
                    . count($data['items']) . ' product(s)'
            );

            return $purchase->load('items.product', 'supplier');
        });
    }

    // ─── Sale (Stock OUT) ─────────────────────────────────────────────────────

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            // Pre-flight: aggregate duplicate product_ids and validate all at once
            // so the user sees all stock errors together, not one at a time.
            $this->validateStockAvailability($data['items']);

            $totalAmount     = 0;
            $totalCommission = 0;
            $itemsToCreate   = [];

            foreach ($data['items'] as $item) {
                $qty            = $item['quantity'];
                $sellingPrice   = $item['selling_price'];
                $dispatchPrice  = $item['dispatch_price'] ?? 0;
                $commissionRate = $item['commission_rate'] ?? 0;

                $lineTotal        = $qty * $sellingPrice;
                $commissionAmount = round($lineTotal * ($commissionRate / 100), 2);

                $totalAmount     += $lineTotal;
                $totalCommission += $commissionAmount;

                $itemsToCreate[] = [
                    'product_id'        => $item['product_id'],
                    'quantity'          => $qty,
                    'selling_price'     => $sellingPrice,
                    'dispatch_price'    => $dispatchPrice,
                    'commission_rate'   => $commissionRate,
                    'commission_amount' => $commissionAmount,
                ];
            }

            $companyReceivable = $totalAmount - $totalCommission;

            $sale = Sale::create([
                'reference'          => Sale::generateReference(),
                'seller_id'          => $data['seller_id'] ?? null,
                'customer_name'      => $data['customer_name'] ?? null,
                'customer_phone'     => $data['customer_phone'] ?? null,
                'sale_date'          => $data['sale_date'],
                'total_amount'       => $totalAmount,
                'seller_commission'  => $totalCommission,
                'commission_status'  => 'pending',
                'company_receivable' => $companyReceivable,
                'notes'              => $data['notes'] ?? null,
            ]);

            foreach ($itemsToCreate as $item) {
                // Row-level lock prevents oversell in concurrent requests.
                // This is the real guard; validateStockAvailability above is
                // for showing all errors at once before any writes happen.
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for \"{$product->name}\". "
                            . "Available: {$product->stock_quantity}, "
                            . "Requested: {$item['quantity']}",
                    ]);
                }

                SaleItem::create([
                    'sale_id'           => $sale->id,
                    'product_id'        => $product->id,
                    'quantity'          => $item['quantity'],
                    'selling_price'     => $item['selling_price'],
                    'dispatch_price'    => $item['dispatch_price'],
                    'cost_price'        => $product->cost_price, // snapshot at time of sale
                    'commission_rate'   => $item['commission_rate'],
                    'commission_amount' => $item['commission_amount'],
                ]);

                $product->decrement('stock_quantity', $item['quantity']);
            }

            // ── Audit log ──────────────────────────────────────────────────
            ActivityLogger::created(
                $sale,
                "Sale {$sale->reference} created — ₹{$totalAmount}, "
                    . count($itemsToCreate) . ' item(s), stock decremented'
            );

            return $sale->load('items.product');
        });
    }

    // ─── Stock Adjustment ─────────────────────────────────────────────────────

    public function adjustStock(
        int $productId,
        string $type,
        int $quantity,
        string $reason,
        ?string $notes,
        int $userId
    ): StockAdjustment {

        return DB::transaction(function () use ($productId, $type, $quantity, $reason, $notes, $userId) {

            $product = Product::where('id', $productId)
                ->lockForUpdate()
                ->firstOrFail();

            if ($type === 'subtract' && $product->stock_quantity < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Cannot subtract {$quantity} — only {$product->stock_quantity} {$product->unit} in stock.",
                ]);
            }

            $before = $product->stock_quantity;

            if ($type === 'add') {
                $product->increment('stock_quantity', $quantity);
            } else {
                $product->decrement('stock_quantity', $quantity);
            }

            $after = $product->fresh()->stock_quantity;

            $adjustment = StockAdjustment::create([
                'product_id'      => $productId,
                'user_id'         => $userId,
                'type'            => $type,
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'reason'          => $reason,
                'notes'           => $notes,
            ]);

            // ── Audit log ──────────────────────────────────────────────────
            ActivityLogger::updated(
                $product,
                "Stock {$type}: {$quantity} unit(s) for \"{$product->name}\" "
                    . "(before: {$before}, after: {$after}) — Reason: {$reason}"
            );

            return $adjustment;
        });
    }

    // ─── Delete / Reverse ─────────────────────────────────────────────────────

    public function deletePurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {

            // Ensure items are loaded inside the transaction
            $purchase->loadMissing('items');

            foreach ($purchase->items as $item) {
                $product = Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // FIX: If stock was already consumed by sales after this purchase
                // was recorded, reversing would push stock negative. Block it.
                if ($product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'purchase' => "Cannot delete purchase {$purchase->reference}: "
                            . "reversing would create negative stock for \"{$product->name}\". "
                            . "Current stock: {$product->stock_quantity}, "
                            . "Purchase qty: {$item->quantity}.",
                    ]);
                }

                $product->decrement('stock_quantity', $item->quantity);
            }

            // ── Audit log before delete (model won't exist after) ──────────
            ActivityLogger::deleted(
                $purchase,
                "Purchase {$purchase->reference} reversed — stock decremented for "
                    . $purchase->items->count() . ' product(s)'
            );

            $purchase->delete();
        });
    }

    public function deleteSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {

            // Ensure items are loaded inside the transaction
            $sale->loadMissing('items');

            foreach ($sale->items as $item) {
                Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->firstOrFail()
                    ->increment('stock_quantity', $item->quantity);
            }

            // ── Audit log before delete (model won't exist after) ──────────
            ActivityLogger::deleted(
                $sale,
                "Sale {$sale->reference} cancelled — stock restored for "
                    . $sale->items->count() . ' product(s)'
            );

            $sale->delete();
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Aggregate quantities per product across all items and validate stock
     * before any DB writes. This gives the user all errors at once instead of
     * failing mid-transaction on the first out-of-stock item.
     */
    private function validateStockAvailability(array $items): void
    {
        $productIds = array_column($items, 'product_id');
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Sum requested quantities per product (handles duplicate product_ids)
        $requested = [];
        foreach ($items as $item) {
            $requested[$item['product_id']] =
                ($requested[$item['product_id']] ?? 0) + $item['quantity'];
        }

        $errors = [];
        foreach ($requested as $productId => $qty) {
            $product = $products->get($productId);
            if (! $product) {
                $errors[] = "Product ID {$productId} not found.";
                continue;
            }
            if ($product->stock_quantity < $qty) {
                $errors[] = "Insufficient stock for \"{$product->name}\". "
                    . "Available: {$product->stock_quantity}, Requested: {$qty}";
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages(['items' => $errors]);
        }
    }
}