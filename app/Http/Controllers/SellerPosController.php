<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\ErpNotification;
use App\Models\Seller;
use App\Models\SellerSale;
use App\Models\SellerSaleItem;
use App\Models\SellerStock;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SellerPosController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        // Seller sees their own POS directly
        if ($user->hasRole('seller')) {
            $seller = Seller::where('user_id', $user->id)->firstOrFail();
            return $this->showPos($seller);
        }

        // Admin/sales exec: must select a seller first
        if (!$request->seller_id) {
            $sellers = Seller::active()->orderBy('name')->get();
            return view('seller-pos.select', compact('sellers'));
        }

        $seller = Seller::findOrFail($request->seller_id);
        return $this->showPos($seller);
    }

    private function showPos(Seller $seller)
    {
        $stock = $seller->stocks()
            ->with('product.category')
            ->where('quantity', '>', 0)
            ->get();

        $categories = $stock->map(fn($s) => $s->product->category)
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        return view('seller-pos.index', compact('seller', 'stock', 'categories'));
    }

    public function sale(Request $request)
    {
        $request->validate([
            'seller_id'             => ['required', 'exists:sellers,id'],
            'customer_name'         => ['nullable', 'string', 'max:255'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        // Verify seller can only use their own POS
        if (auth()->user()->hasRole('seller')) {
            $mySeller = Seller::where('user_id', auth()->id())->first();
            if (!$mySeller || $mySeller->id != $request->seller_id) {
                return response()->json(['success' => false, 'errors' => ['auth' => 'Unauthorized']], 403);
            }
        }

        try {
            $sale = DB::transaction(function () use ($request) {
                $seller          = Seller::find($request->seller_id);
                $totalAmount     = 0;
                $totalCommission = 0;
                $totalCompany    = 0;

                $sale = SellerSale::create([
                    'seller_id'         => $seller->id,
                    'reference'         => SellerSale::generateReference($seller->id),
                    'customer_name'     => $request->customer_name ?? 'Walk-in',
                    'sale_date'         => now()->toDateString(),
                    'total_amount'      => 0,
                    'commission_amount' => 0,
                    'company_amount'    => 0,
                    'payment_status'    => 'paid',
                ]);

                foreach ($request->items as $item) {
                    $ss = SellerStock::where('seller_id', $seller->id)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()->first();

                    if (!$ss || $ss->quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => 'Insufficient stock for one or more items.'
                        ]);
                    }

                    $product        = $ss->product;
                    $qty            = (int)$item['quantity'];
                    $sellingPrice   = (float)$item['selling_price'];
                    $dispatchPrice  = (float)$product->dispatch_price;
                    $commissionRate = (float)$product->commission_rate;
                    $commAmt        = round($qty * $dispatchPrice * ($commissionRate / 100), 2);
                    $subtotal       = $qty * $sellingPrice;

                    SellerSaleItem::create([
                        'seller_sale_id'    => $sale->id,
                        'product_id'        => $product->id,
                        'quantity'          => $qty,
                        'selling_price'     => $sellingPrice,
                        'dispatch_price'    => $dispatchPrice,
                        'commission_rate'   => $commissionRate,
                        'commission_amount' => $commAmt,
                        'subtotal'          => $subtotal,
                    ]);

                    $ss->decrement('quantity', $qty);
                    $totalAmount     += $subtotal;
                    $totalCommission += $commAmt;
                    $totalCompany    += $qty * $dispatchPrice;
                }

                $sale->update([
                    'total_amount'      => $totalAmount,
                    'commission_amount' => $totalCommission,
                    'company_amount'    => $totalCompany,
                ]);

                Commission::create([
                    'seller_id'      => $seller->id,
                    'seller_sale_id' => $sale->id,
                    'amount'         => $totalCommission,
                    'status'         => 'pending',
                ]);

                ActivityLogger::created($sale,
                    "POS sale {$sale->reference} by {$seller->name} — ₹{$totalAmount}"
                );

                return $sale->load('items.product');
            });

            return response()->json([
                'success'    => true,
                'reference'  => $sale->reference,
                'total'      => number_format($sale->total_amount, 2),
                'commission' => number_format($sale->commission_amount, 2),
                'items'      => $sale->items->map(fn($i) => [
                    'name'     => $i->product->name,
                    'qty'      => $i->quantity,
                    'price'    => number_format($i->selling_price, 2),
                    'subtotal' => number_format($i->subtotal, 2),
                ]),
            ]);

        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'errors' => $e->errors()], 422);
        }
    }
}