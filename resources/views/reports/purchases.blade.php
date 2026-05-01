{{-- paste into resources/views/reports/purchases.blade.php --}}
@extends('layouts.app')
@section('title', 'Purchase Report')
@section('heading', 'Purchase Report')

@section('header-actions')
    <a href="{{ route('export.purchases', request()->only('from','to')) }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
        ⬇️ Export CSV
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        @if($from || $to)<a href="{{ route('reports.purchases') }}" class="text-sm text-gray-500 hover:underline">Clear</a>@endif
    </form>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Total Orders</p>
            <p class="text-2xl font-bold text-blue-800 mt-1">{{ $summary['total_orders'] }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-600 font-medium">Total Spent</p>
            <p class="text-2xl font-bold text-red-800 mt-1">₹{{ number_format($summary['total_spent'], 2) }}</p>
        </div>
        <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
            <p class="text-xs text-gray-600 font-medium">Total Units Received</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($summary['total_units']) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Total Cost</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($purchases as $purchase)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-indigo-600">
                        <a href="{{ route('purchases.show', $purchase) }}">{{ $purchase->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $purchase->purchase_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $purchase->supplier->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $purchase->items->count() }}</td>
                    <td class="px-4 py-3 font-semibold text-blue-700">₹{{ number_format($purchase->total_amount, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">No purchases found for this period.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
