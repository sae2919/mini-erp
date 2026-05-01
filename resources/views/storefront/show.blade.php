@extends('layouts.storefront')
@section('title', $product->name)

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">
    <a href="{{ route('shop.index') }}" class="text-sm text-indigo-600 hover:underline mb-4 inline-block">← Back to Shop</a>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
            <div class="bg-gray-50 flex items-center justify-center p-8 min-h-80">
                <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                     class="max-h-64 object-contain">
            </div>
            <div class="p-8">
                <p class="text-sm text-indigo-500 font-medium mb-2">{{ $product->category->name ?? '' }}</p>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
                <p class="text-xs text-gray-400 font-mono mb-4">SKU: {{ $product->sku }}</p>

                <p class="text-3xl font-bold text-indigo-600 mb-4">₹{{ number_format($product->price, 2) }}</p>

                @if($product->description)
                <p class="text-gray-600 text-sm mb-6">{{ $product->description }}</p>
                @endif

                <div class="flex items-center gap-2 mb-6">
                    @if($product->stock_quantity > 0)
                        <span class="px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                            ✅ In Stock ({{ $product->stock_quantity }} {{ $product->unit }})
                        </span>
                    @else
                        <span class="px-3 py-1 text-xs font-semibold bg-red-100 text-red-700 rounded-full">❌ Out of Stock</span>
                    @endif
                </div>

                @if($product->stock_quantity > 0)
                <form method="POST" action="{{ route('cart.add', $product) }}" class="flex gap-3">
                    @csrf
                    <input type="number" name="qty" value="1" min="1" max="{{ $product->stock_quantity }}"
                           class="w-20 border border-gray-300 rounded-lg px-3 py-2 text-sm text-center focus:ring-2 focus:ring-indigo-400">
                    <button type="submit"
                            class="flex-1 bg-indigo-600 text-white py-2 rounded-lg font-medium hover:bg-indigo-700 transition">
                        🛒 Add to Cart
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    @if($related->count())
    <div class="mt-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Related Products</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            @foreach($related as $rel)
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-3 hover:shadow-md transition">
                <a href="{{ route('shop.show', $rel) }}">
                    <img src="{{ $rel->imageUrl() }}" alt="{{ $rel->name }}"
                         class="w-full h-24 object-contain mb-2">
                    <p class="text-sm font-medium text-gray-800 line-clamp-2">{{ $rel->name }}</p>
                    <p class="text-indigo-600 font-bold text-sm mt-1">₹{{ number_format($rel->price, 2) }}</p>
                </a>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
