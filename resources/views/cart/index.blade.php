@extends('layouts.storefront')
@section('title', 'Cart')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-900 mb-6">🛒 Your Cart</h1>

    @if(empty($cart))
    <div class="text-center py-16 bg-white rounded-2xl border border-gray-100">
        <p class="text-5xl mb-4">🛒</p>
        <p class="text-gray-500 font-medium text-lg">Your cart is empty</p>
        <a href="{{ route('shop.index') }}"
           class="mt-4 inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-indigo-700 transition">
            Continue Shopping
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="lg:col-span-2 space-y-3">
            @foreach($cart as $id => $item)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex items-center gap-4">
                <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}"
                     class="w-16 h-16 object-cover rounded-lg bg-gray-50">
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800">{{ $item['name'] }}</p>
                    <p class="text-sm text-indigo-600 font-bold">₹{{ number_format($item['price'], 2) }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <form method="POST" action="{{ route('cart.update') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $id }}">
                        <input type="number" name="qty" value="{{ $item['qty'] }}" min="0"
                               class="w-16 border border-gray-300 rounded-lg px-2 py-1 text-sm text-center"
                               onchange="this.form.submit()">
                    </form>
                    <p class="font-bold text-gray-800 w-20 text-right">
                        ₹{{ number_format($item['price'] * $item['qty'], 2) }}
                    </p>
                    <a href="{{ route('cart.remove', $id) }}" class="text-red-400 hover:text-red-600 text-lg font-bold">×</a>
                </div>
            </div>
            @endforeach

            <form method="POST" action="{{ route('cart.clear') }}" class="text-right">
                @csrf
                <button class="text-sm text-gray-400 hover:text-red-500 transition">Clear Cart</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 h-fit">
            <h2 class="font-semibold text-gray-800 mb-4">Order Summary</h2>
            <div class="flex justify-between text-sm text-gray-600 mb-2">
                <span>Items ({{ $count }})</span>
                <span>₹{{ number_format($total, 2) }}</span>
            </div>
            <div class="flex justify-between text-sm text-gray-600 mb-4">
                <span>Shipping</span>
                <span class="text-green-600">Free</span>
            </div>
            <div class="border-t border-gray-200 pt-4 flex justify-between font-bold text-lg">
                <span>Total</span>
                <span class="text-indigo-700">₹{{ number_format($total, 2) }}</span>
            </div>
            <a href="{{ route('checkout.index') }}"
               class="mt-5 block w-full bg-indigo-600 text-white text-center py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
                Proceed to Checkout →
            </a>
            <a href="{{ route('shop.index') }}"
               class="mt-3 block text-center text-sm text-gray-500 hover:text-indigo-600">
                ← Continue Shopping
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
