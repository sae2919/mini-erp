@extends('layouts.app')
@section('title','New Dispatch')
@section('heading','Create Dispatch Order')
@section('header-actions')
    <a href="{{ route('dispatches.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-4xl">
<form method="POST" action="{{ route('dispatches.store') }}" id="dispatch-form">
@csrf
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

    <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Seller *</label>
            <select name="seller_id" required id="seller-select"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                <option value="">-- Select Seller --</option>
                @foreach($sellers as $s)
                <option value="{{ $s->id }}" {{ request('seller_id')==$s->id?'selected':'' }}>
                    {{ $s->name }} ({{ $s->region }})
                </option>
                @endforeach
            </select>
            @error('seller_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Dispatch Date *</label>
            <input type="date" name="dispatch_date" value="{{ date('Y-m-d') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
        </div>
    </div>

    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium text-gray-800">Products to Dispatch</h3>
            <button type="button" id="add-row"
                    class="px-3 py-1.5 text-sm bg-indigo-50 text-indigo-700 rounded-lg hover:bg-indigo-100 font-medium">
                + Add Product
            </button>
        </div>
        @error('items')<p class="text-red-500 text-sm mb-3">{{ $message }}</p>@enderror

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100 text-xs font-medium">
                    <th class="pb-2 w-2/5">Product (Warehouse Stock)</th>
                    <th class="pb-2">Quantity</th>
                    <th class="pb-2">Dispatch Price (₹)</th>
                    <th class="pb-2">Subtotal</th>
                    <th class="pb-2 w-8"></th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
            <tfoot>
                <tr class="border-t border-gray-200">
                    <td colspan="3" class="pt-3 text-right font-bold text-gray-700">Total:</td>
                    <td class="pt-3 font-bold text-indigo-700" id="grand-total">₹0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="px-6 py-4 bg-blue-50 text-sm text-blue-800">
        📦 Dispatching will deduct from <strong>warehouse stock</strong> and add to the seller's stock.
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('dispatches.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            📤 Create Dispatch Order
        </button>
    </div>
</div>
</form>
</div>
@endsection

@push('scripts')
<script>
// Line 90 becomes much simpler:
const products = @json($products);
let idx = 0;

function addRow() {
    const i    = idx++;
    const opts = products.map(p=>`<option value="${p.id}" data-price="${p.price}" data-stock="${p.stock}">${p.name} — ${p.stock} in stock</option>`).join('');
    const tr   = document.createElement('tr');
    tr.className = 'item-row border-b border-gray-50';
    tr.innerHTML = `
        <td class="py-2 pr-2">
            <select name="items[${i}][product_id]" class="prod-sel w-full border border-gray-300 rounded px-2 py-1.5 text-sm" required>
                <option value="">-- Select --</option>${opts}
            </select>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${i}][quantity]" class="qty w-24 border border-gray-300 rounded px-2 py-1.5 text-sm" min="1" value="1" required>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="items[${i}][dispatch_price]" class="price w-28 border border-gray-300 rounded px-2 py-1.5 text-sm" step="0.01" min="0" required>
        </td>
        <td class="py-2 pr-2"><span class="sub text-sm font-medium text-indigo-700">₹0.00</span></td>
        <td class="py-2"><button type="button" class="del text-red-400 hover:text-red-600 text-lg font-bold">×</button></td>`;
    document.getElementById('items-body').appendChild(tr);

    const sel   = tr.querySelector('.prod-sel');
    const qty   = tr.querySelector('.qty');
    const price = tr.querySelector('.price');
    const sub   = tr.querySelector('.sub');

    sel.addEventListener('change',()=>{
        const o = sel.options[sel.selectedIndex];
        if(!price.value && o.dataset.price) price.value = o.dataset.price;
        if(o.dataset.stock) qty.max = o.dataset.stock;
        calc();
    });
    qty.addEventListener('input',calc);
    price.addEventListener('input',calc);
    tr.querySelector('.del').addEventListener('click',()=>{ tr.remove(); calcTotal(); });

    function calc(){
        sub.textContent='₹'+((parseFloat(qty.value)||0)*(parseFloat(price.value)||0)).toFixed(2);
        calcTotal();
    }
}

function calcTotal(){
    let t=0;
    document.querySelectorAll('.sub').forEach(s=>t+=parseFloat(s.textContent.replace('₹',''))||0);
    document.getElementById('grand-total').textContent='₹'+t.toFixed(2);
}

document.getElementById('add-row').addEventListener('click',addRow);
document.getElementById('dispatch-form').addEventListener('submit',e=>{
    if(!document.querySelectorAll('.item-row').length){ e.preventDefault(); alert('Add at least one product.'); }
});
addRow();
</script>
@endpush
