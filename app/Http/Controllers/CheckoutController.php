<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = CartController::getCart();

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        $total = CartController::cartTotal();
        return view('checkout.index', compact('cart', 'total'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shipping_name'    => ['required', 'string', 'max:255'],
            'shipping_phone'   => ['required', 'string', 'max:20'],
            'shipping_email'   => ['required', 'email', 'max:255'],
            'shipping_address' => ['required', 'string', 'max:500'],
        ]);

        $cart = CartController::getCart();

        if (empty($cart)) {
            return redirect()->route('shop.index')->with('error', 'Your cart is empty.');
        }

        try {
            $sale = DB::transaction(function () use ($validated, $cart) {
                $productIds = array_keys($cart);
                $products   = Product::whereIn('id', $productIds)->get()->keyBy('id');

                foreach ($cart as $id => $item) {
                    $product = $products->get($id);
                    if (!$product || $product->stock_quantity < $item['qty']) {
                        throw ValidationException::withMessages([
                            'cart' => '"' . ($product?->name ?? 'A product') . '" is out of stock.',
                        ]);
                    }
                }

                $totalAmount = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));

                $sale = Sale::create([
                    'reference'        => Sale::generateReference('online'),
                    'sale_date'        => now()->toDateString(),
                    'total_amount'     => $totalAmount,
                    'order_type'       => 'online',
                    'status'           => 'pending',
                    'payment_status'   => 'unpaid',
                    'customer_name'    => $validated['shipping_name'],
                    'shipping_name'    => $validated['shipping_name'],
                    'shipping_phone'   => $validated['shipping_phone'],
                    'shipping_email'   => $validated['shipping_email'],
                    'shipping_address' => $validated['shipping_address'],
                ]);

                foreach ($cart as $id => $item) {
                    $product = Product::where('id', $id)->lockForUpdate()->first();

                    SaleItem::create([
                        'sale_id'       => $sale->id,
                        'product_id'    => $product->id,
                        'quantity'      => $item['qty'],
                        'selling_price' => $item['price'],
                        'cost_price'    => $product->cost_price,
                    ]);

                    $product->decrement('stock_quantity', $item['qty']);
                }

                return $sale;
            });

            session()->forget('cart');

            return redirect()->route('orders.track', $sale->reference)
                ->with('success', 'Order placed! Your order ID is ' . $sale->reference);

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}
