@extends('layouts.app')
@section('title','Best Selling Products')
@section('heading','Best Selling Products')

@section('header-actions')
    <a href="{{ url('/export/best-products?from='.$from.'&to='.$to) }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
        Export Excel
    </a>
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