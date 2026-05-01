@extends('layouts.app')
@section('title', $return->reference)
@section('heading', 'Return — ' . $return->reference)

@section('header-actions')
    <a href="{{ route('returns.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Returns</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Return Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-medium">{{ $return->reference }}</dd>
                <dt class="text-gray-500">Original Sale</dt>
                <dd>
                    <a href="{{ route('sales.show', $return->sale) }}"
                       class="text-indigo-600 hover:underline">{{ $return->sale->reference }}</a>
                </dd>
                <dt class="text-gray-500">Date</dt>
                <dd>{{ $return->return_date->format('d M Y') }}</dd>
                <dt class="text-gray-500">Reason</dt>
                <dd>{{ \App\Models\SaleReturn::reasons()[$return->reason] ?? $return->reason }}</dd>
                <dt class="text-gray-500">Processed By</dt>
                <dd>{{ $return->user?->name ?? '—' }}</dd>
                <dt class="text-gray-500">Notes</dt>
                <dd>{{ $return->notes ?: '—' }}</dd>
            </dl>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-6 text-center flex flex-col justify-center">
            <p class="text-sm text-orange-600 font-medium">Total Refund</p>
            <p class="text-4xl font-bold text-orange-800 mt-2">₹{{ number_format($return->total_amount, 2) }}</p>
            <span class="mt-3 px-3 py-1 text-xs font-semibold rounded-full inline-block
                {{ $return->status === 'approved' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ ucfirst($return->status) }}
            </span>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Returned Items</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">Qty Returned</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($return->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-gray-600">₹{{ number_format($item->price, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-orange-600">₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
