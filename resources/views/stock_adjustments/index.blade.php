@extends('layouts.app')
@section('title', 'Stock Adjustments')
@section('heading', 'Stock Adjustments')

@section('header-actions')
    <a href="{{ route('stock-adjustments.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        + New Adjustment
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Product</label>
            <select name="product_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Products</option>
                @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Type</label>
            <select name="type" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                <option value="add"      {{ request('type') == 'add'      ? 'selected' : '' }}>Added</option>
                <option value="subtract" {{ request('type') == 'subtract' ? 'selected' : '' }}>Removed</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        @if(request()->hasAny(['from','to','product_id','type']))
            <a href="{{ route('stock-adjustments.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Date & Time</th>
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Qty</th>
                    <th class="px-4 py-3">Before → After</th>
                    <th class="px-4 py-3">Reason</th>
                    <th class="px-4 py-3">By</th>
                    <th class="px-4 py-3">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($adjustments as $adj)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-500 text-xs whitespace-nowrap">
                        {{ $adj->created_at->format('d M Y, h:i A') }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('products.show', $adj->product) }}"
                           class="font-medium text-indigo-600 hover:underline">{{ $adj->product->name }}</a>
                        <p class="text-xs text-gray-400">{{ $adj->product->sku }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $adj->type === 'add' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $adj->type === 'add' ? '➕ Added' : '➖ Removed' }}
                        </span>
                    </td>
                    <td class="px-4 py-3 font-bold
                        {{ $adj->type === 'add' ? 'text-green-700' : 'text-red-600' }}">
                        {{ $adj->type === 'add' ? '+' : '-' }}{{ $adj->quantity }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        <span class="font-mono">{{ $adj->quantity_before }}</span>
                        <span class="text-gray-400 mx-1">→</span>
                        <span class="font-mono font-semibold">{{ $adj->quantity_after }}</span>
                        <span class="text-xs text-gray-400">{{ $adj->product->unit }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs bg-gray-100 text-gray-600 rounded-full">
                            {{ \App\Models\StockAdjustment::reasons()[$adj->reason] ?? $adj->reason }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs">{{ $adj->user->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs max-w-xs truncate">{{ $adj->notes ?: '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                        No stock adjustments yet.
                        <a href="{{ route('stock-adjustments.create') }}" class="text-indigo-600">Create one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $adjustments->links() }}</div>
    </div>
</div>
@endsection
