@extends('layouts.app')
@section('title','Record Sale')
@section('heading','Record Seller Sale')
@section('header-actions')
    <a href="{{ route('seller-sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-3xl">
<form method="POST" action="{{ route('seller-sales.store') }}" id="sale-form">
@csrf

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        @if($mySeller)
        <input type="hidden" name="seller_id" value="{{ $mySeller->id }}">
        <div class="md:col-span-2 bg-green-50 border border-green-100 rounded-xl px-4 py-3 text-sm text-green-800">
            📍 Recording sale for: <strong>{{ $mySeller->name }}</strong>
        </div>
        @else
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Seller *</label>
            <select name="seller_id" required id="seller-sel"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
                <option value="">-- Select Seller --</option>
                @foreach($sellers as $s)
                <option value="{{ $s->id }}" {{ old('seller_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Name</label>
            <input type="text" name="customer_name" value="{{ old('customer_name') }}" placeholder="Walk-in if empty"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Customer Phone</label>
            <input type="text" name="customer_phone" value="{{ old('customer_phone') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Sale Date *</label>
            <input type="date" name="sale_date" value="{{ date('Y-m-d') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
    </div>

    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium text-gray-800">Products Sold</h3>
            <button type="button" id="add-row"
                    class="px-3 py-1.5 text-sm bg-green-50 text-green-700 rounded-lg hover:bg-green-100 font-medium">
                + Add Product
            </button>
        </div>
        @error('items')<p class="text-red-500 text-sm mb-3">{{ $message }}</p>@enderror

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100 text-xs font-medium">
                    <th class="pb-2 w-2/5">Product (My Stock)</th>
                    <th class="pb-2">Available</th>
                    <th class="pb-2">Qty</th>
                    <th class="pb-2">Selling Price (₹)</th>
                    <th class="pb-2">Commission</th>
                    <th class="pb-2">Subtotal</th>
                    <th class="pb-2 w-8"></th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
            <tfoot>
                <tr class="border-t border-gray-200">
                    <td colspan="3" class="pt-3 text-right font-bold text-gray-700">Commission Total:</td>
                    <td colspan="2" class="pt-3 font-bold text-purple-600" id="commission-total">₹0.00</td>
                    <td></td>
                </tr>
                <tr>
                    <td colspan="3" class="pt-1 text-right font-bold text-gray-700">Grand Total:</td>
                    <td colspan="2" class="pt-1 font-bold text-green-700" id="grand-total">₹0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('seller-sales.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
            💰 Record Sale
        </button>
    </div>
</div>
</form>
</div>
@endsection

@push('scripts')
<script>
@php
$stockData = isset($stock) ? $stock->map(function($s) {
    return [
        'product_id' => $s->product_id,
        'name'       => $s->product->name.' ('.$s->product->sku.')',
        'available'  => $s->quantity,
        'mrp'        => $s->product->mrp,
        'comm_rate'  => $s->product->commission_rate,
        'disp_price' => $s->product->dispatch_price,
        'unit'       => $s->product->unit,
    ];
}) : collect();
@endphp
const myStock = @json($stockData);

let idx = 0;

// ─────────────────────────────────────────────────────────────────────
// Hides already-selected products from every other row's dropdown.
// ─────────────────────────────────────────────────────────────────────
function refreshSelects() {
    const rows = document.querySelectorAll('.item-row');

    const selected = {};
    rows.forEach(row => {
        const sel = row.querySelector('.prod-sel');
        if (sel.value) selected[sel.value] = sel;
    });

    rows.forEach(row => {
        const sel = row.querySelector('.prod-sel');
        Array.from(sel.options).forEach(opt => {
            if (!opt.value) return;
            const takenByOther = selected[opt.value] && selected[opt.value] !== sel;
            opt.hidden   = takenByOther;
            opt.disabled = takenByOther;
        });
    });
}

function addRow() {
    const i    = idx++;
    const opts = myStock.map(s =>
        `<option value="${s.product_id}"
                 data-avail="${s.available}"
                 data-mrp="${s.mrp}"
                 data-comm="${s.comm_rate}"
                 data-disp="${s.disp_price}"
                 data-unit="${s.unit}">
            ${s.name} (${s.available} left)
        </option>`
    ).join('');

    const tr = document.createElement('tr');
    tr.className = 'item-row border-b border-gray-50';
    tr.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${i}][product_id]"
                    class="prod-sel w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required>
                <option value="">-- Select --</option>${opts}
            </select>
        </td>
        <td class="py-2 pr-2 text-xs text-gray-500 avail-col">—</td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${i}][quantity]"
                   class="qty w-20 border border-gray-300 rounded px-2 py-1.5 text-sm"
                   min="1" value="1" required>
        </td>
        <td class="py-2 pr-2">
            <input type="number"
                   name="items[${i}][selling_price]"
                   class="price w-28 border border-gray-300 rounded px-2 py-1.5 text-sm bg-gray-100 cursor-not-allowed"
                   step="0.01" min="0" readonly required>
        </td>
        <td class="py-2 pr-2 text-xs text-purple-600 comm-col">—</td>
        <td class="py-2 pr-2">
            <span class="sub text-sm font-medium text-green-700">₹0.00</span>
        </td>
        <td class="py-2">
            <button type="button" class="del text-red-400 hover:text-red-600 text-lg font-bold">×</button>
        </td>
        <input type="hidden" name="items[${i}][commission_rate]" class="hidden-comm" value="0">
        <input type="hidden" name="items[${i}][dispatch_price]"  class="hidden-disp" value="0">
    `;

    document.getElementById('items-body').appendChild(tr);
    refreshSelects();

    const sel        = tr.querySelector('.prod-sel');
    const qty        = tr.querySelector('.qty');
    const price      = tr.querySelector('.price');
    const sub        = tr.querySelector('.sub');
    const avail      = tr.querySelector('.avail-col');
    const comm       = tr.querySelector('.comm-col');
    const hiddenComm = tr.querySelector('.hidden-comm');
    const hiddenDisp = tr.querySelector('.hidden-disp');

    sel.addEventListener('change', () => {
        const o = sel.options[sel.selectedIndex];

        avail.textContent = o.dataset.avail ? o.dataset.avail + ' ' + o.dataset.unit : '—';

        // ✅ Auto-fill with MRP (product selling price set by admin)
        price.value      = o.dataset.mrp ? parseFloat(o.dataset.mrp).toFixed(2) : 0;

        qty.max          = o.dataset.avail || 9999;
        hiddenComm.value = o.dataset.comm || 0;
        hiddenDisp.value = o.dataset.disp || 0;

        refreshSelects();
        calc();
    });

    qty.addEventListener('input', calc);

    tr.querySelector('.del').addEventListener('click', () => {
        tr.remove();
        refreshSelects();
        calcTotals();
    });

    function calc() {
        const o   = sel.options[sel.selectedIndex];
        const q   = parseFloat(qty.value) || 0;
        const max = parseFloat(o.dataset.avail) || 0;

        if (q > max) {
            qty.value = max;
            alert(`Only ${max} items available in stock`);
            return;
        }

        const p  = parseFloat(price.value) || 0;
        // Commission is calculated on dispatch price (company's charge to seller)
        const d  = parseFloat(o.dataset.disp) || p;
        const cr = parseFloat(o.dataset.comm) || 0;

        const commAmt = q * d * (cr / 100);

        sub.textContent  = '₹' + (q * p).toFixed(2);
        comm.textContent = cr > 0 ? `${cr}% = ₹${commAmt.toFixed(2)}` : '—';

        hiddenComm.value = cr;
        hiddenDisp.value = d;

        calcTotals();
    }
}

function calcTotals() {
    let grandTotal      = 0;
    let commissionTotal = 0;

    document.querySelectorAll('.item-row').forEach(row => {
        const sub       = parseFloat(row.querySelector('.sub').textContent.replace('₹', '')) || 0;
        const commText  = row.querySelector('.comm-col').textContent;
        const commMatch = commText.match(/₹([\d.]+)/);
        const comm      = commMatch ? parseFloat(commMatch[1]) : 0;

        grandTotal      += sub;
        commissionTotal += comm;
    });

    document.getElementById('grand-total').textContent      = '₹' + grandTotal.toFixed(2);
    document.getElementById('commission-total').textContent = '₹' + commissionTotal.toFixed(2);
}

document.getElementById('add-row').addEventListener('click', addRow);

document.getElementById('sale-form').addEventListener('submit', e => {
    if (!document.querySelectorAll('.item-row').length) {
        e.preventDefault();
        alert('Add at least one product.');
    }
});

addRow();
</script>
@endpush