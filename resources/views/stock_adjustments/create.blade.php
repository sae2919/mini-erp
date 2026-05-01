@extends('layouts.app')
@section('title', 'Stock Adjustment')
@section('heading', 'New Stock Adjustment')
@section('header-actions')
    <a href="{{ route('stock-adjustments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-2xl">
<form method="POST" action="{{ route('stock-adjustments.store') }}">
@csrf

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

    <div class="p-6 space-y-5">

        {{-- Product --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Product *</label>
            <select name="product_id" id="product-select" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                           @error('product_id') border-red-400 @enderror">
                <option value="">-- Select Product --</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}"
                            data-stock="{{ $p->stock_quantity }}"
                            data-unit="{{ $p->unit }}"
                            {{ old('product_id', $selectedProduct?->id) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} ({{ $p->sku }})
                    </option>
                @endforeach
            </select>
            @error('product_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror

            {{-- Live stock display --}}
            <div id="stock-info" class="mt-2 text-sm text-gray-500 hidden">
                Current stock: <span id="current-stock" class="font-semibold text-gray-800"></span>
                <span id="stock-unit"></span>
            </div>
        </div>

        {{-- Type --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Adjustment Type *</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="type" value="add"
                           {{ old('type', 'add') === 'add' ? 'checked' : '' }}
                           class="text-green-600 focus:ring-green-500">
                    <span class="text-sm font-medium text-green-700 bg-green-50 px-3 py-1 rounded-full">
                        ➕ Add Stock
                    </span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="type" value="subtract"
                           {{ old('type') === 'subtract' ? 'checked' : '' }}
                           class="text-red-600 focus:ring-red-500">
                    <span class="text-sm font-medium text-red-700 bg-red-50 px-3 py-1 rounded-full">
                        ➖ Remove Stock
                    </span>
                </label>
            </div>
            @error('type')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Quantity --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity *</label>
            <input type="number" name="quantity" value="{{ old('quantity', 1) }}" min="1" required
                   class="w-40 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                          @error('quantity') border-red-400 @enderror">
            @error('quantity')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Reason --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
            <select name="reason" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                           @error('reason') border-red-400 @enderror">
                <option value="">-- Select Reason --</option>
                @foreach(\App\Models\StockAdjustment::reasons() as $value => $label)
                    <option value="{{ $value }}" {{ old('reason') == $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="3" placeholder="Additional details about this adjustment..."
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('notes') }}</textarea>
        </div>

        {{-- Preview --}}
        <div id="preview" class="hidden bg-gray-50 rounded-lg px-4 py-3 text-sm text-gray-700 border border-gray-200">
            After adjustment: <span id="preview-text" class="font-semibold"></span>
        </div>

    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('stock-adjustments.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            Record Adjustment
        </button>
    </div>
</div>
</form>
</div>
@endsection

@push('scripts')
<script>
const productSelect = document.getElementById('product-select');
const stockInfo     = document.getElementById('stock-info');
const currentStock  = document.getElementById('current-stock');
const stockUnit     = document.getElementById('stock-unit');
const preview       = document.getElementById('preview');
const previewText   = document.getElementById('preview-text');
const qtyInput      = document.querySelector('input[name="quantity"]');
const typeInputs    = document.querySelectorAll('input[name="type"]');

function updatePreview() {
    const opt   = productSelect.options[productSelect.selectedIndex];
    const stock = parseInt(opt.dataset.stock);
    const unit  = opt.dataset.unit || '';
    const qty   = parseInt(qtyInput.value) || 0;
    const type  = document.querySelector('input[name="type"]:checked')?.value;

    if (!opt.value || !qty || !type) { preview.classList.add('hidden'); return; }

    const after = type === 'add' ? stock + qty : stock - qty;
    const color = after < 0 ? 'text-red-600' : (after <= 10 ? 'text-yellow-600' : 'text-green-700');

    previewText.innerHTML = `<span class="${color}">${after} ${unit}</span>
        <span class="text-gray-400 font-normal">(was ${stock} ${unit})</span>`;
    preview.classList.remove('hidden');
}

productSelect.addEventListener('change', () => {
    const opt = productSelect.options[productSelect.selectedIndex];
    if (opt.value) {
        currentStock.textContent = opt.dataset.stock;
        stockUnit.textContent    = opt.dataset.unit;
        stockInfo.classList.remove('hidden');
    } else {
        stockInfo.classList.add('hidden');
    }
    updatePreview();
});

qtyInput.addEventListener('input', updatePreview);
typeInputs.forEach(r => r.addEventListener('change', updatePreview));

// Trigger on load if product pre-selected
if (productSelect.value) productSelect.dispatchEvent(new Event('change'));
</script>
@endpush
