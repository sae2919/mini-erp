{{-- resources/views/reports/profit.blade.php --}}
@extends('layouts.app')
@section('title', 'Profit Report')
@section('heading', 'Profit Report')

@section('header-actions')
    <a href="{{ route('export.profit', request()->only('from','to')) }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
        ⬇️ Export CSV
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    {{-- Filter Form --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from ?? '' }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to ?? '' }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">
            Filter
        </button>
        @if($from || $to)
            <a href="{{ route('reports.profit') }}" class="text-sm text-gray-500 hover:underline self-center">
                Clear
            </a>
        @endif
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Total Revenue</p>
            <p class="text-xl font-bold text-green-800 mt-1">₹{{ number_format($summary['total_revenue'], 2) }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-600 font-medium">Total Cost</p>
            <p class="text-xl font-bold text-red-800 mt-1">₹{{ number_format($summary['total_cost'], 2) }}</p>
        </div>
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            <p class="text-xs text-indigo-600 font-medium">Net Profit</p>
            <p class="text-xl font-bold {{ $summary['total_profit'] >= 0 ? 'text-indigo-800' : 'text-red-800' }} mt-1">
                ₹{{ number_format($summary['total_profit'], 2) }}
            </p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
            <p class="text-xs text-yellow-600 font-medium">Profit Margin</p>
            <p class="text-xl font-bold text-yellow-800 mt-1">{{ $summary['margin_pct'] }}%</p>
        </div>
    </div>

    {{-- Note: profit uses cost_price SNAPSHOT from sale_items, not live product cost --}}
    <div class="bg-blue-50 border border-blue-100 rounded-lg px-4 py-2 text-xs text-blue-700">
        ℹ️ Profit is calculated using the cost price recorded <strong>at the time of each sale</strong>,
        not the product's current cost price. This ensures historical accuracy.
    </div>

    {{-- Product Breakdown Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">
            Product-wise Breakdown
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Units Sold</th>
                    <th class="px-4 py-3">Revenue</th>
                    <th class="px-4 py-3">Cost</th>
                    <th class="px-4 py-3">Profit</th>
                    <th class="px-4 py-3">Margin %</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($rows as $row)
                @php
                    $margin = $row->total_revenue > 0
                        ? round(($row->total_profit / $row->total_revenue) * 100, 1)
                        : 0;
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row->product_name }}</td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $row->sku }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ number_format($row->total_units_sold) }}</td>
                    <td class="px-4 py-3 text-green-700 font-medium">₹{{ number_format($row->total_revenue, 2) }}</td>
                    <td class="px-4 py-3 text-red-600">₹{{ number_format($row->total_cost, 2) }}</td>
                    <td class="px-4 py-3 font-bold {{ $row->total_profit >= 0 ? 'text-indigo-700' : 'text-red-700' }}">
                        ₹{{ number_format($row->total_profit, 2) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $margin >= 20
                                ? 'bg-green-100 text-green-700'
                                : ($margin >= 0
                                    ? 'bg-yellow-100 text-yellow-700'
                                    : 'bg-red-100 text-red-700') }}">
                            {{ $margin }}%
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        No sales data for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection