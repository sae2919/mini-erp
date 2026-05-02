@extends('layouts.app')
@section('title',$production->reference)
@section('heading','Production — '.$production->reference)
@section('header-actions')
    <a href="{{ route('productions.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Batch Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt><dd class="font-mono font-bold text-blue-600">{{ $production->reference }}</dd>
                <dt class="text-gray-500">Date</dt><dd>{{ $production->production_date->format('d M Y') }}</dd>
                <dt class="text-gray-500">Recorded By</dt><dd>{{ $production->user?->name ?? '—' }}</dd>
                <dt class="text-gray-500">Notes</dt><dd>{{ $production->notes ?: '—' }}</dd>
            </dl>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-center">
            <p class="text-sm text-blue-600 font-medium">Total Production Cost</p>
            <p class="text-4xl font-bold text-blue-800 mt-2">₹{{ number_format($production->total_cost, 2) }}</p>
            <p class="text-sm text-blue-500 mt-1">{{ $production->items->sum('quantity') }} units produced</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Products Manufactured</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2">Quantity</th>
                    <th class="px-4 py-2">Unit Cost</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($production->items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->product->sku }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->product->category?->name ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }} {{ $item->product->unit }}</td>
                    <td class="px-4 py-3 text-gray-600">₹{{ number_format($item->unit_cost, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-blue-700">₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total</td>
                    <td class="px-4 py-3 font-bold text-blue-800 text-base">₹{{ number_format($production->total_cost, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
