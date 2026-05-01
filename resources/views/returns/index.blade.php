@extends('layouts.app')
@section('title', 'Returns')
@section('heading', 'Returns & Refunds')

@section('content')
<div class="py-4 space-y-4">

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Original Sale</th>
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
                    <td class="px-4 py-3 font-medium text-indigo-600">{{ $return->reference }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('sales.show', $return->sale) }}"
                           class="text-indigo-600 hover:underline">{{ $return->sale->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $return->return_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ \App\Models\SaleReturn::reasons()[$return->reason] ?? $return->reason }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-orange-600">
                        ₹{{ number_format($return->total_amount, 2) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $return->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($return->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $return->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('returns.show', $return) }}"
                           class="text-xs text-indigo-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                        No returns yet. Returns are created from individual sale pages.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $returns->links() }}</div>
    </div>
</div>
@endsection
