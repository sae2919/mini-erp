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

                Product::where('id', $item['product_id'])->lockForUpdate()->first()
                    ->increment('stock_quantity', $item['quantity']);
            }

            return $purchase->load('items.product', 'supplier');
        });
    }

    // ─── Sale (Stock OUT) ─────────────────────────────────────────────────────

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            $this->validateStockAvailability($data['items']);

            $totalAmount = collect($data['items'])->sum(
                fn($item) => $item['quantity'] * $item['selling_price']
            );

            $sale = Sale::create([
                'reference'     => Sale::generateReference(),
                'customer_name' => $data['customer_name'] ?? null,
                'sale_date'     => $data['sale_date'],
                'total_amount'  => $totalAmount,
                'notes'         => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($product->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for \"{$product->name}\".
                                    Available: {$product->stock_quantity},
                                    Requested: {$item['quantity']}",
                    ]);
                }

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'quantity'      => $item['quantity'],
                    'selling_price' => $item['selling_price'],
                    'cost_price'    => $product->cost_price,
                ]);

                $product->decrement('stock_quantity', $item['quantity']);
            }

            return $sale->load('items.product');
        });
    }

    // ─── Stock Adjustment ─────────────────────────────────────────────────────

    /**
     * Manually adjust stock up or down with a full audit trail.
     */
    public function adjustStock(
        int $productId,
        string $type,
        int $quantity,
        string $reason,
        ?string $notes,
        int $userId
    ): StockAdjustment {

        return DB::transaction(function () use ($productId, $type, $quantity, $reason, $notes, $userId) {

            $product = Product::where('id', $productId)->lockForUpdate()->firstOrFail();

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

            return StockAdjustment::create([
                'product_id'      => $productId,
                'user_id'         => $userId,
                'type'            => $type,
                'quantity'        => $quantity,
                'quantity_before' => $before,
                'quantity_after'  => $after,
                'reason'          => $reason,
                'notes'           => $notes,
            ]);
        });
    }

    // ─── Delete / Reverse ─────────────────────────────────────────────────────

    public function deletePurchase(Purchase $purchase): void
    {
        DB::transaction(function () use ($purchase) {
            foreach ($purchase->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                if ($product->stock_quantity < $item->quantity) {
                    throw ValidationException::withMessages([
                        'purchase' => "Cannot delete: reversing this purchase would create
                                       negative stock for \"{$product->name}\".",
                    ]);
                }

                $product->decrement('stock_quantity', $item->quantity);
            }

            $purchase->delete();
        });
    }

    public function deleteSale(Sale $sale): void
    {
        DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                Product::where('id', $item->product_id)
                    ->lockForUpdate()
                    ->first()
                    ->increment('stock_quantity', $item->quantity);
            }

            $sale->delete();
        });
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    private function validateStockAvailability(array $items): void
    {
        $productIds = array_column($items, 'product_id');
        $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $requested = [];
        foreach ($items as $item) {
            $requested[$item['product_id']] = ($requested[$item['product_id']] ?? 0) + $item['quantity'];
        }

        $errors = [];
        foreach ($requested as $productId => $qty) {
            $product = $products->get($productId);
            if (!$product) {
                $errors[] = "Product ID {$productId} not found.";
                continue;
            }
            if ($product->stock_quantity < $qty) {
                $errors[] = "Insufficient stock for \"{$product->name}\".
                             Available: {$product->stock_quantity}, Requested: {$qty}";
            }
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages(['items' => $errors]);
        }
    }
}
