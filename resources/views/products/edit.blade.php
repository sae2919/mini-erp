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

        {{-- Name --}}
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- SKU --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">SKU *</label>
            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        {{-- Category --}}
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

        {{-- ── PRICING SECTION ────────────────────────────── --}}
        @if($canEditPricing)

        {{-- Production Cost --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Production Cost (₹)</label>
            <input type="number" name="production_cost"
                   value="{{ old('production_cost', $product->production_cost ?? 0) }}"
                   step="0.01" min="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Cost to manufacture per unit</p>
        </div>

        {{-- Dispatch Price --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dispatch Price (₹) *</label>
            <input type="number" name="dispatch_price"
                   value="{{ old('dispatch_price', $product->dispatch_price ?? 0) }}"
                   step="0.01" min="0" id="dispatch-price"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Price charged to seller</p>
        </div>

        {{-- MRP --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">MRP (₹) *</label>
            <input type="number" name="mrp"
                   value="{{ old('mrp', $product->mrp ?? 0) }}"
                   step="0.01" min="0" id="mrp"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Max retail price (seller to customer)</p>
        </div>

        {{-- Commission Rate --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Commission Rate (%)</label>
            <input type="number" name="commission_rate"
                   value="{{ old('commission_rate', $product->commission_rate ?? 0) }}"
                   step="0.01" min="0" max="100" id="comm-rate"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Seller earns this % of dispatch price</p>
        </div>

        {{-- Commission Preview --}}
        <div class="md:col-span-2 bg-indigo-50 border border-indigo-100 rounded-lg px-4 py-3">
            <p class="text-xs font-semibold text-indigo-700 mb-1">💰 Commission Preview</p>
            <p class="text-sm text-indigo-800">
                Per unit commission =
                <span id="comm-preview" class="font-bold">₹{{ number_format(($product->dispatch_price ?? 0) * (($product->commission_rate ?? 0) / 100), 2) }}</span>
                <span class="text-xs text-indigo-500 ml-2">(Dispatch Price × Commission Rate)</span>
            </p>
        </div>

        {{-- Old price fields --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Selling Price (₹)</label>
            <input type="number" name="price" value="{{ old('price', $product->price) }}" step="0.01" min="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Legacy field (for old sales system)</p>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cost Price (₹)</label>
            <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" step="0.01" min="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            <p class="text-xs text-gray-400 mt-1">Legacy field (for old sales system)</p>
        </div>

        @else
        {{-- Non-admin sees locked pricing --}}
        <div class="md:col-span-2 bg-gray-50 border border-gray-200 rounded-lg px-4 py-3">
            <p class="text-xs text-gray-500 mb-2 font-medium">Pricing (Admin only)</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><span class="text-gray-400">Dispatch:</span> <strong>₹{{ number_format($product->dispatch_price ?? 0, 2) }}</strong></div>
                <div><span class="text-gray-400">MRP:</span> <strong>₹{{ number_format($product->mrp ?? 0, 2) }}</strong></div>
                <div><span class="text-gray-400">Commission:</span> <strong>{{ $product->commission_rate ?? 0 }}%</strong></div>
                <div><span class="text-gray-400">Per unit:</span> <strong>₹{{ number_format(($product->dispatch_price ?? 0) * (($product->commission_rate ?? 0) / 100), 2) }}</strong></div>
            </div>
        </div>
        @endif

        {{-- Unit --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Unit *</label>
            <select name="unit" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                @foreach(['pcs','kg','litre','box','ream','dozen','metre'] as $unit)
                    <option value="{{ $unit }}" {{ old('unit', $product->unit) == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                @endforeach
            </select>
        </div>

        {{-- Low Stock Threshold --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Low Stock Threshold</label>
            <input type="number" name="low_stock_threshold"
                   value="{{ old('low_stock_threshold', $product->low_stock_threshold) }}" min="0" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        {{-- Description --}}
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('description', $product->description) }}</textarea>
        </div>
        {{-- Add Stock --}}
<div>
    <label class="block text-sm font-medium text-gray-700 mb-1">Add Stock</label>
    <input type="number" name="add_stock" min="0" placeholder="Enter quantity to add"
           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
    <p class="text-xs text-gray-400 mt-1">This will increase current stock</p>
</div>
        {{-- Stock note --}}
        <div class="md:col-span-2 bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 text-sm text-yellow-800">
            ⚠️ Stock (<strong>{{ $product->stock_quantity }} {{ $product->unit }}</strong>) is managed via Productions and Dispatches only.
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

@push('scripts')
@if($canEditPricing)
<script>
function updatePreview() {
    const dispatch = parseFloat(document.getElementById('dispatch-price').value) || 0;
    const rate     = parseFloat(document.getElementById('comm-rate').value) || 0;
    const comm     = (dispatch * rate / 100).toFixed(2);
    document.getElementById('comm-preview').textContent = '₹' + comm;
}
document.getElementById('dispatch-price').addEventListener('input', updatePreview);
document.getElementById('comm-rate').addEventListener('input', updatePreview);
</script>
@endif
@endpush