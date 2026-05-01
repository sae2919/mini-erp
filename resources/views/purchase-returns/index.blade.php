@extends('layouts.app')
@section('title', 'Purchase Returns')
@section('heading', 'Purchase Returns')

@section('content')
<div class="py-4 space-y-4">

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-5">
            <p class="text-xs text-orange-600 font-medium uppercase tracking-wide">Total Returned (All Time)</p>
            <p class="text-2xl font-bold text-orange-800 mt-1">₹{{ number_format($totalReturned, 2) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-5">
            <p class="text-xs text-yellow-600 font-medium uppercase tracking-wide">This Month</p>
            <p class="text-2xl font-bold text-yellow-800 mt-1">₹{{ number_format($thisMonthReturn, 2) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <p class="text-xs text-blue-600 font-medium uppercase tracking-wide">Total Return Orders</p>
            <p class="text-2xl font-bold text-blue-800 mt-1">{{ $totalCount }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>Pending</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
        </div>
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
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">
            Filter
        </button>
        @if(request()->hasAny(['status','from','to']))
            <a href="{{ route('purchase-returns.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Purchase</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Reason</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">By</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($returns as $return)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-orange-600 font-mono">
                        {{ $return->reference }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('purchases.show', $return->purchase) }}"
                           class="text-indigo-600 hover:underline">
                            {{ $return->purchase->reference }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $return->supplier?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $return->return_date->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ \App\Models\PurchaseReturn::reasons()[$return->reason] ?? $return->reason }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-orange-600">
                        ₹{{ number_format($return->total_amount, 2) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $return->statusColor() }}">
                            {{ ucfirst($return->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-500">
                        {{ $return->user?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('purchase-returns.show', $return) }}"
                           class="text-xs text-indigo-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">
                        No purchase returns yet. Returns are created from individual purchase pages.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
