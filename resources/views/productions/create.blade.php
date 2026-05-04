@extends('layouts.app')
@section('title','New Production')
@section('heading','Record Production Batch')
@section('header-actions')
    <a href="{{ route('productions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')

{{-- ✅ WRAPPER ADDED --}}
<div class="grid grid-cols-2 gap-6">

{{-- ================= LEFT SIDE (UNCHANGED) ================= --}}
<div class="py-4 max-w-3xl">
<form method="POST" action="{{ route('productions.store') }}" id="prod-form">
@csrf
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 grid grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Production Date *</label>
            <input type="date" name="production_date" value="{{ date('Y-m-d') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes" placeholder="Batch notes..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-400 focus:border-transparent">
        </div>
    </div>

    <div class="p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-medium text-gray-800">Products Manufactured</h3>
            <button type="button" id="add-row"
                    class="px-3 py-1.5 text-sm bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 font-medium">
                + Add Product
            </button>
        </div>

        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100 text-xs font-medium">
                    <th class="pb-2 w-1/2">Product</th>
                    <th class="pb-2">Quantity</th>
                    <th class="pb-2">Unit Cost (₹)</th>
                    <th class="pb-2">Subtotal</th>
                    <th class="pb-2 w-8"></th>
                </tr>
            </thead>
            <tbody id="items-body"></tbody>
            <tfoot>
                <tr class="border-t border-gray-200">
                    <td colspan="3" class="pt-3 text-right font-bold text-gray-700">Total Cost:</td>
                    <td class="pt-3 font-bold text-blue-700" id="grand-total">₹0.00</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('productions.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-blue-600 text-white rounded-lg font-medium hover:bg-blue-700 transition">
            🏭 Record Production
        </button>
    </div>
</div>
</form>
</div>

{{-- ================= RIGHT SIDE (NEW REPORT) ================= --}}
<div class="bg-white rounded-xl shadow border border-gray-100 p-6">

    <h3 class="text-lg font-semibold mb-4">📊 Stock Movement Report</h3>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Product</th>
                <th>Produced</th>
                <th>Dispatched</th>
                <th>Sold</th>
                <th>Warehouse</th>
                <th>Total</th>
            </tr>
        </thead>

        <tbody>
            @forelse($report as $row)
            <tr class="border-b">
                <td class="py-2">{{ $row->product_name }}</td>
                <td>{{ $row->produced }}</td>
                <td class="text-blue-600 font-medium">{{ $row->dispatched }}</td>
                <td class="text-green-600 font-medium">{{ $row->sold }}</td>
                <td>{{ $row->warehouse }}</td>
                <td class="font-bold">{{ $row->total }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-4 text-gray-400">
                    No data available
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>

</div>
@endsection


@push('scripts')
<script>
const products = @json($products->map(fn($p)=>['id'=>$p->id,'name'=>$p->name.' ('.$p->sku.')','cost'=>$p->production_cost]));
let idx = 0;

function addRow() {
    const i    = idx++;
    const opts = products.map(p=>`<option value="${p.id}" data-cost="${p.cost}">${p.name}</option>`).join('');
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
            <input type="number" name="items[${i}][unit_cost]" class="cost w-28 border border-gray-300 rounded px-2 py-1.5 text-sm" step="0.01" min="0" required>
        </td>
        <td class="py-2 pr-2"><span class="sub text-sm font-medium text-blue-700">₹0.00</span></td>
        <td class="py-2"><button type="button" class="del text-red-400 hover:text-red-600 text-lg font-bold">×</button></td>`;
    document.getElementById('items-body').appendChild(tr);

    const sel  = tr.querySelector('.prod-sel');
    const qty  = tr.querySelector('.qty');
    const cost = tr.querySelector('.cost');
    const sub  = tr.querySelector('.sub');

    sel.addEventListener('change',()=>{ const o=sel.options[sel.selectedIndex]; if(!cost.value&&o.dataset.cost) cost.value=o.dataset.cost; calc(); });
    qty.addEventListener('input',calc);
    cost.addEventListener('input',calc);
    tr.querySelector('.del').addEventListener('click',()=>{ tr.remove(); calcTotal(); });

    function calc(){ sub.textContent='₹'+((parseFloat(qty.value)||0)*(parseFloat(cost.value)||0)).toFixed(2); calcTotal(); }
}

function calcTotal(){
    let t=0;
    document.querySelectorAll('.sub').forEach(s=>t+=parseFloat(s.textContent.replace('₹',''))||0);
    document.getElementById('grand-total').textContent='₹'+t.toFixed(2);
}

document.getElementById('add-row').addEventListener('click',addRow);
document.getElementById('prod-form').addEventListener('submit',e=>{ if(!document.querySelectorAll('.item-row').length){ e.preventDefault(); alert('Add at least one product.'); } });
addRow();
</script>
@endpush