@extends('layouts.app')
@section('title', 'Products')
@section('heading', 'Products')

@section('header-actions')
    @role('admin|inventory_manager')
    <a href="{{ route('products.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        + Add Product
    </a>
    @endrole
@endsection

@section('content')
<div class="py-4 space-y-4">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or SKU"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-48 focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Category</label>
            <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Stock</label>
            <select name="stock_status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Low Stock</option>
                <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Out of Stock</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        @if(request()->hasAny(['search','category_id','stock_status']))
            <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Price (₹)</th>
                    @if($showCostPrice)
                    <th class="px-4 py-3">Cost (₹)</th>
                    <th class="px-4 py-3">Margin</th>
                    @endif
                    <th class="px-4 py-3">Stock</th>
                    @role('admin|inventory_manager')
                    <th class="px-4 py-3">Actions</th>
                    @endrole
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('products.show', $product) }}"
                           class="font-medium text-indigo-600 hover:underline">{{ $product->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $product->sku }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $product->category->name ?? '—' }}</td>
                    <td class="px-4 py-3 font-medium text-green-700">₹{{ number_format($product->price, 2) }}</td>
                    @if($showCostPrice)
                    <td class="px-4 py-3 text-gray-500">₹{{ number_format($product->cost_price, 2) }}</td>
                    <td class="px-4 py-3 text-indigo-600 text-xs font-medium">{{ $product->profitMargin() }}%</td>
                    @endif
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $product->stock_quantity == 0
                                ? 'bg-red-100 text-red-700'
                                : ($product->isLowStock() ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700') }}">
                            {{ $product->stock_quantity }} {{ $product->unit }}
                        </span>
                    </td>
                    @role('admin|inventory_manager')
                    <td class="px-4 py-3 flex gap-2">
                        <a href="{{ route('products.edit', $product) }}"
                           class="text-xs text-indigo-600 hover:underline">Edit</a>
                        @role('admin')
                        <form method="POST" action="{{ route('products.destroy', $product) }}"
                              onsubmit="return confirm('Delete this product?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                        @endrole
                    </td>
                    @endrole
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                        No products found.
                        @role('admin|inventory_manager')
                        <a href="{{ route('products.create') }}" class="text-indigo-600">Add one</a>.
                        @endrole
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
@endsection
