@extends('layouts.app')
@section('title', 'New Purchase')
@section('heading', 'New Purchase')
@section('header-actions')
    <a href="{{ route('purchases.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Purchases</a>
@endsection

@section('content')
<div class="py-4 max-w-4xl">
<form method="POST" action="{{ route('purchases.store') }}" id="purchase-form">
@csrf

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier *</label>
            <select name="supplier_id" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent @error('supplier_id') border-red-400 @enderror">
                <option value="">-- Select Supplier --</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ old('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            @error('supplier_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Purchase Date *</label>
            <input type="date" name="purchase_date" value="{{ old('purchase_date', date('Y-m-d')) }}"
                   max="{{ date('Y-m-d') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes" value="{{ old('notes') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
    </div>

    {{-- SKU Search --}}
    <div class="px-6 py-3 bg-blue-50 flex items-center gap-3">
        <span class="text-sm font-medium text-blue-700">🔍 SKU Search:</span>
        <input type="text" id="sku-search" placeholder="Type or scan SKU and press Enter"
               class="flex-1 border border-blue-200 bg-white rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent">
        <span id="sku-result" class="text-xs text-gray-500"></span>
    </div>

    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium text-gray-800">Line Items</h3>
            <button type="button" id="add-item"
                    class="text-sm bg-blue-50 text-blue-700 hover:bg-blue-100 px-3 py-1.5 rounded-lg font-medium transition">
                + Add Product
            </button>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium w-2/5">Product</th>
                    <th class="pb-2 font-medium">Current Stock</th>
                    <th class="pb-2 font-medium">Qty</th>
                    <th class="pb-2 font-medium">Cost Price (₹)</th>
                    <th class="pb-2 font-medium">Subtotal</th>
                    <th class="pb-2 w-10"></th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
            <tfoot>
                <tr class="border-t border-gray-200">
                    <td colspan="4" class="pt-3 text-right font-semibold text-gray-700">Total:</td>
                    <td class="pt-3 font-bold text-gray-900" id="grand-total">₹0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('purchases.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
            🛒 Record Purchase
        </button>
    </div>
</div>
</form>
</div>
@endsection

@push('scripts')
<script>
const products = [
    @foreach($products as $p)
    { id: {{ $p->id }}, name: "{{ addslashes($p->name) }}", sku: "{{ $p->sku }}", stock: {{ $p->stock_quantity }}, cost_price: {{ $p->cost_price }} },
    @endforeach
];

const skuMap = {};
products.forEach(p => skuMap[p.sku.toLowerCase()] = p);

let rowIndex = 0;

function addRow(preset = {}) {
    const idx  = rowIndex++;
    const opts = products.map(p =>
        `<option value="${p.id}" data-stock="${p.stock}" data-cost="${p.cost_price}"
                 ${preset.id == p.id ? 'selected' : ''}>${p.name} (${p.sku})</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.className = 'item-row border-b border-gray-50';
    tr.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${idx}][product_id]" class="product-select w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required>
                <option value="">-- Select --</option>${opts}
            </select>
        </td>
        <td class="py-2 pr-2"><span class="stock-info text-xs text-gray-500">—</span></td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${idx}][quantity]" class="qty-input w-20 border border-gray-300 rounded px-2 py-1.5 text-sm" min="1" value="${preset.qty||1}" required>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${idx}][cost_price]" class="cost-input w-24 border border-gray-300 rounded px-2 py-1.5 text-sm" step="0.01" min="0.01" value="${preset.cost||''}" required>
        </td>
        <td class="py-2 pr-2"><span class="subtotal text-sm font-medium text-gray-700">₹0.00</span></td>
        <td class="py-2"><button type="button" class="remove-row text-red-400 hover:text-red-600 text-lg font-bold">×</button></td>
    `;

    document.getElementById('items-body').appendChild(tr);

    const sel  = tr.querySelector('.product-select');
    const qty  = tr.querySelector('.qty-input');
    const cost = tr.querySelector('.cost-input');
    const sub  = tr.querySelector('.subtotal');
    const info = tr.querySelector('.stock-info');

    function recalc() {
        sub.textContent = '₹' + ((parseFloat(qty.value)||0) * (parseFloat(cost.value)||0)).toFixed(2);
        updateTotal();
    }

    sel.addEventListener('change', () => {
        const opt = sel.options[sel.selectedIndex];
        info.textContent = opt.value ? opt.dataset.stock + ' in stock' : '—';
        if (!cost.value && opt.dataset.cost) cost.value = opt.dataset.cost;
        recalc();
    });

    qty.addEventListener('input', recalc);
    cost.addEventListener('input', recalc);
    tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); updateTotal(); });

    if (preset.id) sel.dispatchEvent(new Event('change'));
}

function updateTotal() {
    let t = 0;
    document.querySelectorAll('.subtotal').forEach(el => t += parseFloat(el.textContent.replace('₹',''))||0);
    document.getElementById('grand-total').textContent = '₹' + t.toFixed(2);
}

document.getElementById('sku-search').addEventListener('keydown', function(e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const product = skuMap[this.value.trim().toLowerCase()];
    const result  = document.getElementById('sku-result');
    if (!product) {
        result.textContent = '❌ SKU not found'; result.className = 'text-xs text-red-500';
        return;
    }
    addRow({ id: product.id, cost: product.cost_price });
    result.textContent = '✅ Added: ' + product.name; result.className = 'text-xs text-green-600';
    this.value = '';
    setTimeout(() => result.textContent = '', 3000);
});

document.getElementById('add-item').addEventListener('click', () => addRow());
document.getElementById('purchase-form').addEventListener('submit', e => {
    if (!document.querySelectorAll('.item-row').length) { e.preventDefault(); alert('Add at least one product.'); }
});
addRow();
</script>
@endpush
