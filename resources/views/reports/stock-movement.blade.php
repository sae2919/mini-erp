@extends('layouts.app')
@section('title','Stock Movement')
@section('heading','Stock Movement Report')

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Category</label>
            <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $c)<option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Generate</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3 text-center bg-blue-50">🏭 Produced</th>
                    <th class="px-4 py-3 text-center bg-indigo-50">📤 Dispatched</th>
                    <th class="px-4 py-3 text-center bg-green-50">💰 Sold</th>
                    <th class="px-4 py-3 text-center bg-gray-50">🏪 Warehouse</th>
                    <th class="px-4 py-3 text-center bg-orange-50">📦 With Sellers</th>
                    <th class="px-4 py-3 text-center bg-purple-50">Total Stock</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($products as $p)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <p class="font-medium text-gray-800">{{ $p['product']->name }}</p>
                        <p class="text-xs text-gray-400 font-mono">{{ $p['product']->sku }}</p>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $p['product']->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-center font-medium text-blue-700 bg-blue-50/30">
                        {{ number_format($p['produced']) }}
                    </td>
                    <td class="px-4 py-3 text-center font-medium text-indigo-700 bg-indigo-50/30">
                        {{ number_format($p['dispatched']) }}
                    </td>
                    <td class="px-4 py-3 text-center font-medium text-green-700 bg-green-50/30">
                        {{ number_format($p['sold']) }}
                    </td>
                    <td class="px-4 py-3 text-center font-medium {{ $p['warehouse'] <= $p['product']->low_stock_threshold ? 'text-red-600' : 'text-gray-700' }} bg-gray-50/30">
                        {{ number_format($p['warehouse']) }}
                        @if($p['warehouse'] <= $p['product']->low_stock_threshold)
                        <span class="text-xs">⚠️</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-orange-700 bg-orange-50/30">
                        {{ number_format($p['seller_stock']) }}
                    </td>
                    <td class="px-4 py-3 text-center font-bold text-purple-700 bg-purple-50/30">
                        {{ number_format($p['total_stock']) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No stock movement data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                <tr class="font-bold text-gray-700">
                    <td colspan="2" class="px-4 py-3">Total</td>
                    <td class="px-4 py-3 text-center text-blue-700">{{ number_format($products->sum('produced')) }}</td>
                    <td class="px-4 py-3 text-center text-indigo-700">{{ number_format($products->sum('dispatched')) }}</td>
                    <td class="px-4 py-3 text-center text-green-700">{{ number_format($products->sum('sold')) }}</td>
                    <td class="px-4 py-3 text-center">{{ number_format($products->sum('warehouse')) }}</td>
                    <td class="px-4 py-3 text-center text-orange-700">{{ number_format($products->sum('seller_stock')) }}</td>
                    <td class="px-4 py-3 text-center text-purple-700">{{ number_format($products->sum('total_stock')) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
