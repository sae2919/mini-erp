<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public static function getCart(): array
    {
        return session('cart', []);
    }

    public static function cartCount(): int
    {
        return array_sum(array_column(session('cart', []), 'qty'));
    }

    public static function cartTotal(): float
    {
        return array_sum(array_map(
            fn($item) => $item['price'] * $item['qty'],
            session('cart', [])
        ));
    }

    public function index()
    {
        $cart  = self::getCart();
        $total = self::cartTotal();
        $count = self::cartCount();
        return view('cart.index', compact('cart', 'total', 'count'));
    }

    public function add(Request $request, Product $product)
    {
        $request->validate(['qty' => ['required', 'integer', 'min:1']]);
        $qty = (int) $request->qty;

        if ($product->stock_quantity < $qty) {
            return back()->with('error', 'Insufficient stock for "' . $product->name . '".');
        }

        $cart = self::getCart();
        $id   = (string) $product->id;

        if (isset($cart[$id])) {
            $newQty = $cart[$id]['qty'] + $qty;
            if ($newQty > $product->stock_quantity) {
                return back()->with('error', 'Only ' . $product->stock_quantity . ' units available.');
            }
            $cart[$id]['qty'] = $newQty;
        } else {
            $cart[$id] = [
                'id'    => $product->id,
                'name'  => $product->name,
                'sku'   => $product->sku,
                'price' => (float) $product->price,
                'image' => $product->imageUrl(),
                'qty'   => $qty,
                'unit'  => $product->unit,
            ];
        }

        session(['cart' => $cart]);
        return back()->with('success', '"' . $product->name . '" added to cart.');
    }

    public function update(Request $request)
    {
        $request->validate(['product_id' => 'required', 'qty' => 'required|integer|min:0']);

        $cart = self::getCart();
        $id   = (string) $request->product_id;

        if ($request->qty == 0) {
            unset($cart[$id]);
        } elseif (isset($cart[$id])) {
            $product = Product::find($id);
            if ($product && $request->qty <= $product->stock_quantity) {
                $cart[$id]['qty'] = (int) $request->qty;
            } else {
                return back()->with('error', 'Requested quantity not available.');
            }
        }

        session(['cart' => $cart]);
        return back()->with('success', 'Cart updated.');
    }

    public function remove(string $productId)
    {
        $cart = self::getCart();
        unset($cart[$productId]);
        session(['cart' => $cart]);
        return back()->with('success', 'Item removed from cart.');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared.');
    }
}
