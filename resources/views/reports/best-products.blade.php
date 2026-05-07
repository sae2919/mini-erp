@extends('layouts.app')
@section('title','Best Selling Products')
@section('heading','Best Selling Products')

@section('header-actions')

<form method="GET" action="{{ route('export.best-products-excel') }}">

    <input type="hidden" name="from" value="{{ request('from') }}">
    <input type="hidden" name="to" value="{{ request('to') }}">

    <div style="position:relative;display:inline-block;">

        <button type="button"
            onclick="toggleExportOptions()"
            style="
                background:#16a34a;
                color:white;
                padding:10px 18px;
                border:none;
                border-radius:10px;
                cursor:pointer;
                font-weight:600;
            ">
            Export Excel ▼
        </button>

        <div id="exportOptions"
            style="
                display:none;
                position:absolute;
                right:0;
                top:55px;
                background:white;
                border:1px solid #e5e7eb;
                border-radius:14px;
                padding:18px;
                width:340px;
                box-shadow:0 10px 30px rgba(0,0,0,0.12);
                z-index:999;
            ">

            <!-- PRODUCTS -->
            <div style="
                margin-bottom:14px;
                font-size:15px;
                font-weight:700;
                color:#111827;
            ">
                Select Products
            </div>

            <!-- ALL PRODUCTS -->

            <div class="toggle-row">

                <span>All Products</span>

                <label class="switch">

                    <input
                        type="checkbox"
                        id="allProductToggle"
                        checked
                    >

                    <span class="slider"></span>

                </label>

            </div>

            <!-- PRODUCT LIST -->

           @foreach($products as $p)

<div class="toggle-row">

    <span>{{ $p->product->name }}</span>

    <label class="switch">

        <input
            type="checkbox"
            class="product-checkbox"
            name="product_ids[]"
            value="{{ $p->product->id }}"
            checked
        >

        <span class="slider"></span>

    </label>

</div>

@endforeach

            <!-- COLUMNS -->

            <div style="
                margin-top:20px;
                margin-bottom:14px;
                font-size:15px;
                font-weight:700;
                color:#111827;
            ">
                Select Columns
            </div>

            <div class="toggle-row">
                <span>Product</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="product" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Category</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="category" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Units Sold</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="units" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Revenue</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="revenue" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Commission</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="commission" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Orders</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="orders" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Share %</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="share" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <!-- BUTTON -->

            <button type="submit"
                style="
                    margin-top:22px;
                    width:100%;
                    background:#2563eb;
                    color:white;
                    border:none;
                    padding:12px;
                    border-radius:10px;
                    cursor:pointer;
                    font-weight:600;
                ">
                Generate Export
            </button>

        </div>

    </div>

</form>

<style>

.toggle-row{
    display:flex;
    align-items:center;
    justify-content:space-between;
    margin-bottom:14px;
    font-size:14px;
    color:#374151;
}

.switch{
    position:relative;
    display:inline-block;
    width:46px;
    height:24px;
}

.switch input{
    opacity:0;
    width:0;
    height:0;
}

.slider{
    position:absolute;
    cursor:pointer;
    top:0;
    left:0;
    right:0;
    bottom:0;
    background-color:#d1d5db;
    transition:.3s;
    border-radius:50px;
}

.slider:before{
    position:absolute;
    content:"";
    height:18px;
    width:18px;
    left:3px;
    bottom:3px;
    background:white;
    transition:.3s;
    border-radius:50%;
}

.switch input:checked + .slider{
    background:#2563eb;
}

.switch input:checked + .slider:before{
    transform:translateX(22px);
}

</style>

<script>

function toggleExportOptions() {

    const box = document.getElementById('exportOptions');

    if (box.style.display === 'none' || box.style.display === '') {
        box.style.display = 'block';
    } else {
        box.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function () {

    const allToggle = document.getElementById('allProductToggle');

    const productCheckboxes = document.querySelectorAll('.product-checkbox');

    allToggle.addEventListener('change', function () {

        productCheckboxes.forEach(cb => {

            cb.checked = allToggle.checked;

        });

    });

    productCheckboxes.forEach(cb => {

        cb.addEventListener('change', function () {

            let allChecked = true;

            productCheckboxes.forEach(item => {

                if (!item.checked) {
                    allChecked = false;
                }

            });

            allToggle.checked = allChecked;

        });

    });

});

</script>

@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Generate</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Units Sold</th>
                    <th class="px-4 py-3">Revenue</th>
                    <th class="px-4 py-3">Commission</th>
                    <th class="px-4 py-3">Orders</th>
                    <th class="px-4 py-3">Share %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $i => $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-400 font-medium">{{ $i + 1 }}</td>
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p->product->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $p->product->sku }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $p->product->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 font-bold text-gray-800">{{ number_format($p->total_qty) }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">₹{{ number_format($p->total_revenue, 0) }}</td>
                    <td class="px-4 py-3 text-purple-600">₹{{ number_format($p->total_commission, 0) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $p->order_count }}</td>
                    <td class="px-4 py-3">
                        @php $share = $totalRevenue > 0 ? round(($p->total_revenue / $totalRevenue) * 100, 1) : 0; @endphp
                        <div class="flex items-center gap-2">
                            <div class="w-16 bg-gray-200 rounded-full h-1.5">
                                <div class="bg-indigo-600 h-1.5 rounded-full" style="width:{{ $share }}%"></div>
                            </div>
                            <span class="text-xs text-gray-600">{{ $share }}%</span>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No sales data for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection