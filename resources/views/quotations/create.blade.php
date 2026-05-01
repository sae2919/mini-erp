@extends('layouts.app')
@section('title', 'New Quotation')
@section('heading', 'New Quotation')

@section('header-actions')
    <a href="{{ route('quotations.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-4xl">
<form method="POST" action="{{ route('quotations.store') }}" id="quotation-form">
@csrf

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name *</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Email</label>
            <input type="email" name="customer_email" value="{{ old('customer_email') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Phone</label>
            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Quotation Date *</label>
            <input type="date" name="quotation_date" value="{{ old('quotation_date', date('Y-m-d')) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valid Until *</label>
            <input type="date" name="valid_until" value="{{ old('valid_until', date('Y-m-d', strtotime('+30 days'))) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes" value="{{ old('notes') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
    </div>

    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium text-gray-800">Line Items</h3>
            <button type="button" id="add-item"
                    class="text-sm bg-indigo-50 text-indigo-700 hover:bg-indigo-100 px-3 py-1.5 rounded-lg font-medium transition">
                + Add Product
            </button>
        </div>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium w-2/5">Product</th>
                    <th class="pb-2 font-medium">Qty</th>
                    <th class="pb-2 font-medium">Unit Price (₹)</th>
                    <th class="pb-2 font-medium">Discount %</th>
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
        <a href="{{ route('quotations.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit" name="action" value="draft"
                class="px-4 py-2 text-sm bg-gray-600 text-white rounded-lg font-medium hover:bg-gray-700 transition">
            Save Draft
        </button>
        <button type="submit" name="action" value="send"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            📋 Save & Send
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
    { id: {{ $p->id }}, name: "{{ addslashes($p->name) }} ({{ $p->sku }})", price: {{ $p->price }} },
    @endforeach
];

let rowIndex = 0;

function addRow() {
    const idx = rowIndex++;
    const opts = products.map(p =>
        `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.className = 'item-row border-b border-gray-50';
    tr.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${idx}][product_id]" class="product-select w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required>
                <option value="">-- Select --</option>${opts}
            </select>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${idx}][quantity]" class="qty w-20 border border-gray-300 rounded px-2 py-1.5 text-sm" min="1" value="1" required>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${idx}][unit_price]" class="price w-28 border border-gray-300 rounded px-2 py-1.5 text-sm" step="0.01" min="0" required>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${idx}][discount]" class="discount w-20 border border-gray-300 rounded px-2 py-1.5 text-sm" step="0.01" min="0" max="100" value="0">
        </td>
        <td class="py-2 pr-2"><span class="subtotal text-sm font-medium text-gray-700">₹0.00</span></td>
        <td class="py-2"><button type="button" class="remove text-red-400 hover:text-red-600 text-lg font-bold">×</button></td>
    `;
    document.getElementById('items-body').appendChild(tr);

    const sel = tr.querySelector('.product-select');
    const qty = tr.querySelector('.qty');
    const price = tr.querySelector('.price');
    const disc = tr.querySelector('.discount');
    const sub  = tr.querySelector('.subtotal');

    function recalc() {
        const q = parseFloat(qty.value) || 0;
        const p = parseFloat(price.value) || 0;
        const d = parseFloat(disc.value) || 0;
        sub.textContent = '₹' + (q * p * (1 - d/100)).toFixed(2);
        updateTotal();
    }

    sel.addEventListener('change', () => {
        const opt = sel.options[sel.selectedIndex];
        if (!price.value && opt.dataset.price) price.value = opt.dataset.price;
        recalc();
    });
    qty.addEventListener('input', recalc);
    price.addEventListener('input', recalc);
    disc.addEventListener('input', recalc);
    tr.querySelector('.remove').addEventListener('click', () => { tr.remove(); updateTotal(); });
}

function updateTotal() {
    let t = 0;
    document.querySelectorAll('.subtotal').forEach(el => t += parseFloat(el.textContent.replace('₹',''))||0);
    document.getElementById('grand-total').textContent = '₹' + t.toFixed(2);
}

document.getElementById('add-item').addEventListener('click', addRow);
document.getElementById('quotation-form').addEventListener('submit', e => {
    if (!document.querySelectorAll('.item-row').length) { e.preventDefault(); alert('Add at least one item.'); }
});
addRow();
</script>
@endpush
