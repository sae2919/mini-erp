@extends('layouts.app')
@section('title', 'Add Product')
@section('heading', 'Add Product')

@section('header-actions')
    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Products</a>
@endsection

@section('content')
<div class="py-4 max-w-2xl">
<form method="POST" action="{{ route('products.store') }}">
@csrf

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                          @error('name') border-red-400 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
            <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="e.g. ELEC-001"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                          @error('sku') border-red-400 @enderror">
            @error('sku')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
            <select name="category_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                           @error('category_id') border-red-400 @enderror">
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (₹) *</label>
            <input type="number" name="price" value="{{ old('price') }}" step="0.01" min="0" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                          @error('price') border-red-400 @enderror">
            @error('price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (₹) *</label>
            <input type="number" name="cost_price" value="{{ old('cost_price') }}" step="0.01" min="0" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                          @error('cost_price') border-red-400 @enderror">
            @error('cost_price')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
            <select name="unit"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                @foreach(['pcs','kg','litre','box','ream','dozen','metre'] as $unit)
                    <option value="{{ $unit }}" {{ old('unit','pcs') == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold *</label>
            <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 10) }}" min="0" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Alert when stock falls below this number</p>
        </div>

        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('description') }}</textarea>
        </div>

    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('products.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
            Cancel
        </a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            Save Product
        </button>
    </div>
</div>
</form>
</div>
@endsection
