@extends('layouts.app')
@section('title', 'New Sale')
@section('heading', 'New Sale')
@section('header-actions')
    <a href="{{ route('sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Sales</a>
@endsection

@section('content')
<div class="py-4 max-w-4xl">
<form method="POST" action="{{ route('sales.store') }}" id="sale-form">
@csrf

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

    {{-- Header fields --}}
    <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sale Date *</label>
            <input type="date" name="sale_date" value="{{ old('sale_date', date('Y-m-d')) }}"
                   max="{{ date('Y-m-d') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer</label>
            <select name="customer_id" id="customer-select"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                <option value="">Walk-in / Manual</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                        {{ $c->name }}{{ $c->phone ? ' — '.$c->phone : '' }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
            <input type="text" name="customer_name" id="customer-name" value="{{ old('customer_name') }}"
                   placeholder="Or type manually"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes" value="{{ old('notes') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
    </div>

    {{-- SKU Search Bar --}}
    <div class="px-6 py-3 bg-indigo-50 flex items-center gap-3">
        <span class="text-sm font-medium text-indigo-700">🔍 SKU Search:</span>
        <input type="text" id="sku-search" placeholder="Type or scan SKU and press Enter"
               class="flex-1 border border-indigo-200 bg-white rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        <span id="sku-result" class="text-xs text-gray-500"></span>
    </div>

    {{-- Line Items --}}
    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium text-gray-800">Line Items</h3>
            <button type="button" id="add-item"
                    class="text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-1.5 rounded-lg font-medium transition">
                + Add Product
            </button>
        </div>

        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium w-2/5">Product</th>
                    <th class="pb-2 font-medium">Available</th>
                    <th class="pb-2 font-medium">Qty</th>
                    <th class="pb-2 font-medium">Price (₹)</th>
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
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('sales.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
            💰 Create Sale
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
    { id: {{ $p->id }}, name: "{{ addslashes($p->name) }}", sku: "{{ $p->sku }}", stock: {{ $p->stock_quantity }}, price: {{ $p->price }} },
    @endforeach
];

const skuMap = {};
products.forEach(p => skuMap[p.sku.toLowerCase()] = p);

let rowIndex = 0;

function addRow(preset = {}) {
    const idx  = rowIndex++;
    const opts = products.map(p =>
        `<option value="${p.id}" data-stock="${p.stock}" data-price="${p.price}"
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
        <td class="py-2 pr-2"><span class="stock-badge text-xs text-gray-500">—</span></td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${idx}][quantity]" class="qty-input w-20 border border-gray-300 rounded px-2 py-1.5 text-sm"
                   min="1" value="${preset.qty || 1}" required>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${idx}][selling_price]" class="price-input w-24 border border-gray-300 rounded px-2 py-1.5 text-sm"
                   step="0.01" min="0.01" value="${preset.price || ''}" required>
        </td>
        <td class="py-2 pr-2"><span class="subtotal text-sm font-medium text-gray-700">₹0.00</span></td>
        <td class="py-2"><button type="button" class="remove-row text-red-400 hover:text-red-600 text-lg font-bold leading-none">×</button></td>
    `;

    document.getElementById('items-body').appendChild(tr);
    bindRow(tr);
    if (preset.id) tr.querySelector('.product-select').dispatchEvent(new Event('change'));
    updateTotal();
}

function bindRow(tr) {
    const sel   = tr.querySelector('.product-select');
    const qty   = tr.querySelector('.qty-input');
    const price = tr.querySelector('.price-input');
    const sub   = tr.querySelector('.subtotal');
    const badge = tr.querySelector('.stock-badge');

    function recalc() {
        const q = parseFloat(qty.value) || 0;
        const p = parseFloat(price.value) || 0;
        sub.textContent = '₹' + (q * p).toFixed(2);
        updateTotal();
    }

    sel.addEventListener('change', () => {
        const opt   = sel.options[sel.selectedIndex];
        const stock = parseInt(opt.dataset.stock || 0);
        badge.textContent = opt.value ? stock + ' in stock' : '—';
        badge.className   = 'stock-badge text-xs font-medium ' + (stock > 0 ? 'text-green-600' : 'text-red-600');
        if (!price.value && opt.dataset.price) price.value = opt.dataset.price;
        qty.max = stock;
        recalc();
    });

    qty.addEventListener('input', recalc);
    price.addEventListener('input', recalc);
    tr.querySelector('.remove-row').addEventListener('click', () => { tr.remove(); updateTotal(); });
}

function updateTotal() {
    let t = 0;
    document.querySelectorAll('.subtotal').forEach(el => t += parseFloat(el.textContent.replace('₹','')) || 0);
    document.getElementById('grand-total').textContent = '₹' + t.toFixed(2);
}

// ── SKU Search ──────────────────────────────────────────────────
document.getElementById('sku-search').addEventListener('keydown', function(e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    const sku     = this.value.trim().toLowerCase();
    const product = skuMap[sku];
    const result  = document.getElementById('sku-result');

    if (!product) {
        result.textContent = '❌ SKU not found: ' + this.value;
        result.className   = 'text-xs text-red-500';
        return;
    }

    if (product.stock === 0) {
        result.textContent = '⚠️ ' + product.name + ' is out of stock';
        result.className   = 'text-xs text-yellow-600';
        return;
    }

    addRow({ id: product.id, price: product.price, qty: 1 });
    result.textContent = '✅ Added: ' + product.name;
    result.className   = 'text-xs text-green-600';
    this.value = '';
    setTimeout(() => result.textContent = '', 3000);
});

// ── Customer auto-fill name ─────────────────────────────────────
document.getElementById('customer-select').addEventListener('change', function() {
    const nameInput = document.getElementById('customer-name');
    const text = this.options[this.selectedIndex].text;
    if (this.value) {
        nameInput.value = text.split(' — ')[0];
        nameInput.readOnly = true;
        nameInput.classList.add('bg-gray-50');
    } else {
        nameInput.value = '';
        nameInput.readOnly = false;
        nameInput.classList.remove('bg-gray-50');
    }
});

document.getElementById('add-item').addEventListener('click', () => addRow());
document.getElementById('sale-form').addEventListener('submit', e => {
    if (!document.querySelectorAll('.item-row').length) {
        e.preventDefault(); alert('Add at least one product.');
    }
});

addRow();
</script>
@endpush
