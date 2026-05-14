@extends('layouts.app')

@section('title','Seller P&L Report')
@section('heading','Seller P&L Report')

@section('header-actions')

<div class="relative inline-block text-left">

    <button
        type="button"
        onclick="toggleExportDropdown()"
        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-xl font-semibold flex items-center gap-2 shadow-md transition"
    >
        Export Excel

        <svg xmlns="http://www.w3.org/2000/svg"
             class="h-4 w-4"
             fill="none"
             viewBox="0 0 24 24"
             stroke="currentColor">

            <path stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M19 9l-7 7-7-7"/>

        </svg>

    </button>

    <div
        id="exportDropdown"
        class="hidden fixed top-24 right-6 w-96 bg-white rounded-2xl shadow-2xl border z-[9999] p-5 overflow-y-auto"
        style="
            max-height:80vh;
            overflow-y:auto;
        "
    >

        <!-- SELLERS -->
        <h3 class="text-lg font-bold mb-4">
            Select Sellers
        </h3>

        <div class="space-y-4 mb-6">

            <label class="flex items-center justify-between">

                <span class="font-medium text-gray-700">
                    All Sellers
                </span>

                <input
                    type="checkbox"
                    id="allSellersToggle"
                    class="toggle-switch"
                >

            </label>

            @foreach($sellers as $seller)

                <label class="flex items-center justify-between">

                    <span class="text-gray-700">
                        {{ $seller->name }}
                    </span>

                    <input
                        type="checkbox"
                        name="seller_ids[]"
                        value="{{ $seller->id }}"
                        class="seller-toggle toggle-switch"
                    >

                </label>

            @endforeach

        </div>

        <!-- COLUMNS -->
        <h3 class="text-lg font-bold mb-4">
            Select Columns
        </h3>

        <div class="space-y-4">

            @php
                $columns = [
                    'seller' => 'Seller',
                    'region' => 'Region',
                    'dispatched' => 'Dispatched',
                    'collected' => 'Collected',
                    'outstanding' => 'Outstanding',
                    'sales' => 'Sales',
                    'commission' => 'Commission',
                    'balance' => 'Balance',
                ];
            @endphp

            @foreach($columns as $value => $label)

                <label class="flex items-center justify-between">

                    <span class="text-gray-700">
                        {{ $label }}
                    </span>

                    <input
                        type="checkbox"
                        class="column-toggle toggle-switch"
                        value="{{ $value }}"
                        checked
                    >

                </label>

            @endforeach

        </div>

        <!-- EXPORT BUTTON -->
        <button
            onclick="submitExport()"
            class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-xl font-semibold shadow-md transition"
        >
            Generate Export
        </button>

    </div>

</div>

<style>

.toggle-switch{
    appearance:none;
    width:52px;
    height:30px;
    background:#d1d5db;
    border-radius:999px;
    position:relative;
    cursor:pointer;
    transition:0.3s;
    flex-shrink:0;
}

.toggle-switch:checked{
    background:#2563eb;
}

.toggle-switch::before{
    content:'';
    position:absolute;
    width:24px;
    height:24px;
    border-radius:50%;
    background:white;
    top:3px;
    left:4px;
    transition:0.3s;
}

.toggle-switch:checked::before{
    transform:translateX(20px);
}

#exportDropdown{
    scrollbar-width:thin;
    scrollbar-color:#cbd5e1 transparent;
}

#exportDropdown::-webkit-scrollbar{
    width:8px;
}

#exportDropdown::-webkit-scrollbar-track{
    background:transparent;
}

#exportDropdown::-webkit-scrollbar-thumb{
    background:#cbd5e1;
    border-radius:20px;
}

#exportDropdown label{
    gap:12px;
}

</style>

@endsection

@section('content')

<div class="py-4 space-y-4">

    <form
        method="GET"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end"
    >

        <div>

            <label class="block text-xs text-gray-500 mb-1">
                Seller
            </label>

            <select
                name="seller_id"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >

                <option value="">
                    All Sellers
                </option>

                @foreach($sellers as $s)

                    <option
                        value="{{ $s->id }}"
                        {{ request('seller_id')==$s->id?'selected':'' }}
                    >

                        {{ $s->name }}

                    </option>

                @endforeach

            </select>

        </div>

       <div>

    <label class="block text-xs text-gray-500 mb-1">
        From
    </label>

    <input
        type="date"
        id="fromDate"
        name="from"
        value="{{ $from }}"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
    >

</div>

<div>

    <label class="block text-xs text-gray-500 mb-1">
        To
    </label>

    <input
        type="date"
        id="toDate"
        name="to"
        value="{{ $to }}"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
    >

</div>

        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">

            Generate

        </button>

    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 border-b border-gray-100">

                <tr class="text-left text-gray-500 font-medium">

                    <th class="px-4 py-3">Seller</th>
                    <th class="px-4 py-3">Region</th>
                    <th class="px-4 py-3">Dispatched</th>
                    <th class="px-4 py-3">Collected</th>
                    <th class="px-4 py-3">Outstanding</th>
                    <th class="px-4 py-3">Sales to Customers</th>
                    <th class="px-4 py-3">Commission Earned</th>
                    <th class="px-4 py-3">Balance</th>

                </tr>

            </thead>

            <tbody class="divide-y divide-gray-50">

                @forelse($data as $row)

                <tr class="hover:bg-gray-50">

                    <td class="px-4 py-3">

                        <a
                            href="{{ route('sellers.show', $row['seller']) }}"
                            class="font-medium text-indigo-600 hover:underline"
                        >

                            {{ $row['seller']->name }}

                        </a>

                    </td>

                    <td class="px-4 py-3 text-gray-500">

                        {{ $row['seller']->region ?: '—' }}

                    </td>

                    <td class="px-4 py-3 text-blue-700 font-medium">

                        ₹{{ number_format($row['dispatched'], 0) }}

                    </td>

                    <td class="px-4 py-3 text-green-700 font-medium">

                        ₹{{ number_format($row['collected'], 0) }}

                    </td>

                    <td class="px-4 py-3 font-semibold {{ $row['outstanding'] > 0 ? 'text-red-600' : 'text-gray-400' }}">

                        ₹{{ number_format($row['outstanding'], 0) }}

                    </td>

                    <td class="px-4 py-3 text-gray-700">

                        ₹{{ number_format($row['sales'], 0) }}

                    </td>

                    <td class="px-4 py-3 text-purple-600 font-medium">

                        ₹{{ number_format($row['commission'], 0) }}

                    </td>

                    <td class="px-4 py-3 font-bold {{ $row['net'] > 0 ? 'text-red-600' : 'text-green-600' }}">

                        ₹{{ number_format(abs($row['net']), 0) }}

                        <span class="text-xs font-normal">

                            {{ $row['net'] > 0 ? 'receivable' : 'clear' }}

                        </span>

                    </td>

                </tr>

                @empty

                <tr>

                    <td
                        colspan="8"
                        class="px-4 py-8 text-center text-gray-400"
                    >

                        No data found.

                    </td>

                </tr>

                @endforelse

            </tbody>

            <tfoot class="border-t-2 border-gray-200 bg-gray-50">

                <tr class="font-bold text-gray-700">

                    <td class="px-4 py-3" colspan="2">
                        Total
                    </td>

                    <td class="px-4 py-3 text-blue-700">
                        ₹{{ number_format($data->sum('dispatched'), 0) }}
                    </td>

                    <td class="px-4 py-3 text-green-700">
                        ₹{{ number_format($data->sum('collected'), 0) }}
                    </td>

                    <td class="px-4 py-3 text-red-600">
                        ₹{{ number_format($data->sum('outstanding'), 0) }}
                    </td>

                    <td class="px-4 py-3">
                        ₹{{ number_format($data->sum('sales'), 0) }}
                    </td>

                    <td class="px-4 py-3 text-purple-600">
                        ₹{{ number_format($data->sum('commission'), 0) }}
                    </td>

                    <td class="px-4 py-3"></td>

                </tr>

            </tfoot>

        </table>

    </div>

</div>

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

function toggleExportDropdown() {

    document
        .getElementById('exportDropdown')
        .classList
        .toggle('hidden');
}

/*
|--------------------------------------------------------------------------
| ALL SELLERS TOGGLE
|--------------------------------------------------------------------------
*/

document
    .getElementById('allSellersToggle')
    .addEventListener('change', function () {

        let checked = this.checked;

        document
            .querySelectorAll('.seller-toggle')
            .forEach(toggle => {

                toggle.checked = checked;
            });
});

/*
|--------------------------------------------------------------------------
| AUTO HANDLE ALL SELLERS TOGGLE
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll('.seller-toggle')
    .forEach(toggle => {

        toggle.addEventListener('change', function () {

            let allSellerToggles =
                document.querySelectorAll('.seller-toggle');

            let checkedSellerToggles =
                document.querySelectorAll('.seller-toggle:checked');

            document.getElementById('allSellersToggle').checked =
                allSellerToggles.length === checkedSellerToggles.length;

        });

});

/*
|--------------------------------------------------------------------------
| EXPORT FUNCTION
|--------------------------------------------------------------------------
*/

function submitExport() {

    let params = new URLSearchParams();

    let from =
        document.querySelector('input[name="from"]').value;

    let to =
        document.querySelector('input[name="to"]').value;

    params.append('from', from);
    params.append('to', to);

    let selectedSellerIds = [];

    document
        .querySelectorAll('.seller-toggle:checked')
        .forEach(toggle => {

            selectedSellerIds.push(toggle.value);

            params.append('seller_ids[]', toggle.value);
        });

    if (selectedSellerIds.length === 0) {

        document
            .querySelectorAll('.seller-toggle')
            .forEach(toggle => {

                params.append('seller_ids[]', toggle.value);
            });
    }

    document
        .querySelectorAll('.column-toggle:checked')
        .forEach(toggle => {

            params.append('columns[]', toggle.value);
        });

    window.location.href =
        "{{ route('export.seller-pnl-excel') }}?"
        + params.toString();
}

/*
|--------------------------------------------------------------------------
| CLOSE DROPDOWN OUTSIDE CLICK
|--------------------------------------------------------------------------
*/

document.addEventListener('click', function(event) {

    const dropdown =
        document.getElementById('exportDropdown');

    const exportButton =
        event.target.closest('[onclick="toggleExportDropdown()"]');

    if (
        !dropdown.contains(event.target)
        &&
        !exportButton
    ) {

        dropdown.classList.add('hidden');
    }
});

/*
|--------------------------------------------------------------------------
| PREVENT DROPDOWN CLOSE INSIDE CLICK
|--------------------------------------------------------------------------
*/

document
    .getElementById('exportDropdown')
    .addEventListener('click', function(event) {

        event.stopPropagation();
});

/*
|--------------------------------------------------------------------------
| ENABLE SMOOTH SCROLL
|--------------------------------------------------------------------------
*/

const exportDropdown =
    document.getElementById('exportDropdown');

exportDropdown.style.maxHeight = '80vh';
exportDropdown.style.overflowY = 'auto';

</script>

@endsection