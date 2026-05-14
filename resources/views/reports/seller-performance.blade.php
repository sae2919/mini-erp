@extends('layouts.app')
@section('title','Seller Performance')
@section('heading','Seller Performance Comparison')
@section('header-actions')

<form method="GET" action="{{ route('export.seller-performance-excel') }}">

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

            <!-- SELLERS -->
            <div style="
                margin-bottom:14px;
                font-size:15px;
                font-weight:700;
                color:#111827;
            ">
                Select Sellers
            </div>

            <!-- ALL SELLERS -->
            <div class="toggle-row">

                <span>All Sellers</span>

                <label class="switch">

                    <input
                        type="checkbox"
                        id="allSellerToggle"
                        checked
                    >

                    <span class="slider"></span>

                </label>

            </div>

            <!-- SELLER LIST -->
            @foreach($sellers as $row)

            <div class="toggle-row">

                <span>{{ $row['seller']->name }}</span>

                <label class="switch">

                    <input
                        type="checkbox"
                        class="seller-checkbox"
                        name="seller_ids[]"
                        value="{{ $row['seller']->id }}"
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

            <!-- COLUMN TOGGLES -->

            <div class="toggle-row">
                <span>Seller</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="seller" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Region</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="region" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Sales</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="sales" checked>
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
                <span>Dispatched</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="dispatched" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Stock</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="stock" checked>
                    <span class="slider"></span>
                </label>
            </div>

            <div class="toggle-row">
                <span>Outstanding</span>
                <label class="switch">
                    <input type="checkbox" name="columns[]" value="outstanding" checked>
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

/* SWITCH */

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

    const allToggle = document.getElementById('allSellerToggle');

    const sellerCheckboxes = document.querySelectorAll('.seller-checkbox');

    // ALL SELLERS TOGGLE

    allToggle.addEventListener('change', function () {

    sellerCheckboxes.forEach(cb => {

        cb.checked = allToggle.checked;

    });

});


    // CHECK ALL STATUS

    sellerCheckboxes.forEach(cb => {

        cb.addEventListener('change', function () {

            let allChecked = true;

            sellerCheckboxes.forEach(item => {

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
             <input
    type="date"
    id="fromDate"
    name="from"
    value="{{ request('from') }}"
    class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
           <input
    type="date"
    id="toDate"
    name="to"
    value="{{ request('to') }}"
    class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Generate</button>
    </form>

    {{-- Chart --}}
    @if($sellers->count())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Sales Comparison</h2>
        <div style="position:relative;height:250px">
            <canvas id="perfChart"></canvas>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Rank</th>
                    <th class="px-4 py-3">Seller</th>
                    <th class="px-4 py-3">Region</th>
                    <th class="px-4 py-3">Sales Count</th>
                    <th class="px-4 py-3">Sales Amount</th>
                    <th class="px-4 py-3">Commission</th>
                    <th class="px-4 py-3">Dispatched</th>
                    <th class="px-4 py-3">Stock (units)</th>
                    <th class="px-4 py-3">Outstanding</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sellers as $i => $row)
                <tr class="hover:bg-gray-50 {{ $i === 0 ? 'bg-yellow-50/50' : '' }}">
                    <td class="px-4 py-3 font-bold text-lg">
                        {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('sellers.show', $row['seller']) }}" class="font-medium text-indigo-600 hover:underline">
                            {{ $row['seller']->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $row['seller']->region ?: '—' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row['sales_count'] }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">₹{{ number_format($row['sales_amount'], 0) }}</td>
                    <td class="px-4 py-3 text-purple-600">₹{{ number_format($row['commission'], 0) }}</td>
                    <td class="px-4 py-3 text-blue-700">₹{{ number_format($row['dispatched'], 0) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ number_format($row['stock_value']) }}</td>
                    <td class="px-4 py-3 font-medium {{ $row['outstanding'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($row['outstanding'], 0) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>

<script>
@if($sellers->count())
new Chart(document.getElementById('perfChart'), {
    type: 'bar',
    data: {
        labels: @json($sellers->pluck('seller')->pluck('name')),
        datasets: [
            {
                label: 'Sales Amount',
                data: @json($sellers->pluck('sales_amount')),
                backgroundColor: 'rgba(34,197,94,0.75)',
                borderRadius: 4
            },
            {
                label: 'Dispatched',
                data: @json($sellers->pluck('dispatched')),
                backgroundColor: 'rgba(99,102,241,0.65)',
                borderRadius: 4
            },
            {
                label: 'Commission',
                data: @json($sellers->pluck('commission')),
                backgroundColor: 'rgba(168,85,247,0.65)',
                borderRadius: 4
            },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    boxWidth: 10
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                ticks: {
                    callback: v => '₹'+(v>=1000?(v/1000).toFixed(0)+'k':v)
                }
            }
        }
    }
});
@endif
</script>
<script>

document.addEventListener('DOMContentLoaded', function () {

    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');

    function updateToDateLimit() {

        if (fromDate.value) {

            toDate.min = fromDate.value;

            if (toDate.value && toDate.value < fromDate.value) {

                toDate.value = fromDate.value;
            }
        }
    }

    updateToDateLimit();

    fromDate.addEventListener('change', updateToDateLimit);

});

</script>
@endpush