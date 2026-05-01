@extends('layouts.storefront')
@section('title', 'Shop')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-6">

    @if(!request('search') && !request('category') && $featured->count())
    <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-8 mb-8 text-white">
        <div class="max-w-lg">
            <p class="text-indigo-200 text-sm font-medium uppercase tracking-wide mb-2">Welcome</p>
            <h1 class="text-3xl font-bold mb-3">Shop the Best Products</h1>
            <p class="text-indigo-100 mb-5">Quality products at great prices</p>
        </div>
    </div>
    @endif

    <div class="flex gap-6">
        <aside class="w-44 flex-shrink-0 hidden md:block">
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <h3 class="font-semibold text-gray-800 mb-3 text-sm">Categories</h3>
                <ul class="space-y-1">
                    <li>
                        <a href="{{ route('shop.index') }}"
                           class="block px-2 py-1.5 rounded-lg text-sm {{ !request('category') ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            All Products
                        </a>
                    </li>
                    @foreach($categories as $cat)
                    <li>
                        <a href="{{ route('shop.index', ['category' => $cat->id]) }}"
                           class="block px-2 py-1.5 rounded-lg text-sm {{ request('category') == $cat->id ? 'bg-indigo-50 text-indigo-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            {{ $cat->name }}
                        </a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        <div class="flex-1">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm text-gray-500">{{ $products->total() }} product(s)
                    @if(request('search')) for "<strong>{{ request('search') }}</strong>"@endif
                </p>
                @if(request('search') || request('category'))
                    <a href="{{ route('shop.index') }}" class="text-xs text-indigo-600 hover:underline">Clear filters</a>
                @endif
            </div>

            @if($products->isEmpty())
            <div class="text-center py-16 bg-white rounded-xl border border-gray-100">
                <p class="text-4xl mb-3">🔍</p>
                <p class="text-gray-500">No products found.</p>
                <a href="{{ route('shop.index') }}" class="text-indigo-600 text-sm hover:underline mt-2 inline-block">Browse all</a>
            </div>
            @else
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($products as $product)
                <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden hover:shadow-md transition group">
                    <a href="{{ route('shop.show', $product) }}">
                        <div class="aspect-square overflow-hidden bg-gray-50">
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition duration-300">
                        </div>
                    </a>
                    <div class="p-3">
                        <p class="text-xs text-indigo-500 font-medium mb-1">{{ $product->category->name ?? '' }}</p>
                        <a href="{{ route('shop.show', $product) }}"
                           class="text-sm font-semibold text-gray-800 hover:text-indigo-600 line-clamp-2 block mb-2">
                            {{ $product->name }}
                        </a>
                        <p class="text-lg font-bold text-indigo-600 mb-3">₹{{ number_format($product->price, 2) }}</p>
                        @if($product->stock_quantity <= 5)
                            <p class="text-xs text-orange-500 mb-2">Only {{ $product->stock_quantity }} left!</p>
                        @endif
                        <form method="POST" action="{{ route('cart.add', $product) }}">
                            @csrf
                            <input type="hidden" name="qty" value="1">
                            <button type="submit"
                                    class="w-full bg-indigo-600 text-white text-xs font-medium py-2 rounded-lg hover:bg-indigo-700 transition">
                                Add to Cart
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            <div class="mt-6">{{ $products->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
