@extends('layouts.app')
@section('title', 'Edit Product')
@section('heading', 'Edit Product')
@section('header-actions')
    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-2xl">
<form method="POST" action="{{ route('products.update', $product) }}">
@csrf @method('PUT')

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <select name="category_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Pricing — Admin only. Inventory Manager sees locked fields --}}
        @if($canEditPricing)
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (₹) *</label>
            <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (₹) *</label>
            <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        @else
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (₹)</label>
            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500">
                ₹{{ number_format($product->price, 2) }}
                <span class="text-xs text-gray-400 ml-2">(admin-only field)</span>
            </div>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (₹)</label>
            <div class="w-full border border-gray-200 bg-gray-50 rounded-lg px-3 py-2 text-sm text-gray-500">
                ₹{{ number_format($product->cost_price, 2) }}
                <span class="text-xs text-gray-400 ml-2">(admin-only field)</span>
            </div>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
            <select name="unit" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @foreach(['pcs','kg','litre','box','ream','dozen','metre'] as $unit)
                    <option value="{{ $unit }}" {{ old('unit', $product->unit) == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
            <input type="number" name="low_stock_threshold"
                   value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-800">
            ⚠️ Stock (<strong>{{ $product->stock_quantity }} {{ $product->unit }}</strong>) is managed via Purchases and Sales only.
        </div>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('products.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            Update Product
        </button>
    </div>
</div>
</form>
</div>
@endsection
