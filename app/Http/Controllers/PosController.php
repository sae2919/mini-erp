<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function index()
    {
        $products   = Product::active()
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('pos.index', compact('products', 'categories'));
    }

    public function sale(Request $request)
    {
        $request->validate([
            'customer_name'             => ['nullable', 'string', 'max:255'],
            'items'                     => ['required', 'array', 'min:1'],
            'items.*.product_id'        => ['required', 'exists:products,id'],
            'items.*.quantity'          => ['required', 'integer', 'min:1'],
            'items.*.selling_price'     => ['required', 'numeric', 'min:0.01'],
        ]);


        try {
            $sale = DB::transaction(function () use ($request) {

                $totalAmount = collect($request->items)->sum(
                    fn($i) => $i['quantity'] * $i['selling_price']
                );

                $sale = Sale::create([
                    'reference'     => Sale::generateReference('pos'),
                    'customer_name' => $request->customer_name ?? 'Walk-in',
                    'sale_date'     => now()->toDateString(),
                    'total_amount'  => $totalAmount,
                    'order_type'    => 'pos',
                    'status'        => 'completed',
                    'payment_status'=> 'paid',  // POS = paid immediately
                ]);
                

                foreach ($request->items as $item) {
                    $product = Product::where('id', $item['product_id'])
                        ->lockForUpdate()->firstOrFail();

                    if ($product->stock_quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock for \"{$product->name}\". Available: {$product->stock_quantity}",
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

            ActivityLogger::created($sale, "POS sale {$sale->reference} — ₹{$sale->total_amount}");

            return response()->json([
                'success'   => true,
                'reference' => $sale->reference,
                'total'     => number_format($sale->total_amount, 2),
                'items'     => $sale->items->map(fn($i) => [
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
