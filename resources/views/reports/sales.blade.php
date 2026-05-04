{{-- resources/views/reports/sales.blade.php --}}
@extends('layouts.app')
@section('title', 'Sales Report')
@section('heading', 'Sales Report')

@section('header-actions')
    <a href="{{ route('export.sales', request()->only('from','to')) }}"
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
            <a href="{{ route('reports.sales') }}" class="text-sm text-gray-500 hover:underline self-center">
                Clear
            </a>
        @endif
    </form>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Total Invoices</p>
            <p class="text-2xl font-bold text-green-800 mt-1">{{ $summary['total_invoices'] }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Total Revenue</p>
            <p class="text-2xl font-bold text-blue-800 mt-1">₹{{ number_format($summary['total_revenue'], 2) }}</p>
        </div>
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            <p class="text-xs text-indigo-600 font-medium">Total Profit</p>
            <p class="text-2xl font-bold text-indigo-800 mt-1">₹{{ number_format($summary['total_profit'], 2) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
            <p class="text-xs text-yellow-600 font-medium">Avg Order Value</p>
            <p class="text-2xl font-bold text-yellow-800 mt-1">₹{{ number_format($summary['avg_order_value'], 2) }}</p>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Revenue</th>
                    <th class="px-4 py-3">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-indigo-600">
                        <a href="{{ route('sales.show', $sale) }}">{{ $sale->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}
                    </td>
                    {{--
                        FIX: replaced $sale->customer_display (accessor that may not exist)
                        with a safe fallback chain.
                    --}}
                    <td class="px-4 py-3 text-gray-600">
                        {{ $sale->customer?->name ?? $sale->customer_name ?? 'Walk-in' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $sale->items->count() }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">
                        ₹{{ number_format($sale->total_amount, 2) }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-indigo-700">
                        {{-- totalProfit() must exist on Sale model --}}
                        ₹{{ number_format($sale->totalProfit(), 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        No sales found for this period.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- FIX: added pagination — without this the entire dataset loads on one page --}}
    @if($sales instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="mt-2">
            {{ $sales->appends(request()->query())->links() }}
        </div>
    @endif

</div>
@endsection