@extends('layouts.app')
@section('title', $purchaseReturn->reference)
@section('heading', 'Purchase Return — ' . $purchaseReturn->reference)

@section('header-actions')
    <a href="{{ route('purchase-returns.index') }}"
       class="text-sm text-gray-500 hover:text-gray-700">← Back to Returns</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Details --}}
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Return Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-bold text-orange-600">{{ $purchaseReturn->reference }}</dd>

                <dt class="text-gray-500">Original Purchase</dt>
                <dd>
                    <a href="{{ route('purchases.show', $purchaseReturn->purchase) }}"
                       class="text-indigo-600 hover:underline">
                        {{ $purchaseReturn->purchase->reference }}
                    </a>
                </dd>

                <dt class="text-gray-500">Supplier</dt>
                <dd class="text-gray-800">{{ $purchaseReturn->supplier?->name ?? '—' }}</dd>

                <dt class="text-gray-500">Return Date</dt>
                <dd class="text-gray-800">{{ $purchaseReturn->return_date->format('d M Y') }}</dd>

                <dt class="text-gray-500">Reason</dt>
                <dd class="text-gray-800">
                    {{ \App\Models\PurchaseReturn::reasons()[$purchaseReturn->reason] ?? $purchaseReturn->reason }}
                </dd>

                <dt class="text-gray-500">Processed By</dt>
                <dd class="text-gray-800">{{ $purchaseReturn->user?->name ?? '—' }}</dd>

                @if($purchaseReturn->notes)
                <dt class="text-gray-500">Notes</dt>
                <dd class="text-gray-800">{{ $purchaseReturn->notes }}</dd>
                @endif
            </dl>
        </div>

        {{-- Summary card --}}
        <div class="space-y-4">
            <div class="bg-orange-50 border border-orange-100 rounded-xl p-6 text-center">
                <p class="text-sm text-orange-600 font-medium">Total Return Value</p>
                <p class="text-4xl font-bold text-orange-800 mt-2">
                    ₹{{ number_format($purchaseReturn->total_amount, 2) }}
                </p>
                <span class="mt-3 px-3 py-1 text-xs font-semibold rounded-full inline-block
                    {{ $purchaseReturn->statusColor() }}">
                    {{ ucfirst($purchaseReturn->status) }}
                </span>
            </div>

            <div class="bg-white border border-gray-100 rounded-xl p-4 text-sm">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Stock Impact</p>
                <div class="flex items-center gap-2 text-red-600">
                    <span class="text-lg">📦↓</span>
                    <span class="font-medium">
                        {{ $purchaseReturn->items->sum('quantity') }} units deducted
                    </span>
                </div>
                <p class="text-xs text-gray-400 mt-1">Stock was reduced on approval</p>
            </div>
        </div>
    </div>

    {{-- Items table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">
            Returned Items
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Qty Returned</th>
                    <th class="px-4 py-2">Unit Price</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($purchaseReturn->items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->product->sku }}</td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $item->quantity }} {{ $item->product->unit }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">₹{{ number_format($item->price, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-orange-600">
                        ₹{{ number_format($item->subtotal, 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-700">Total</td>
                    <td class="px-4 py-3 font-bold text-orange-700 text-base">
                        ₹{{ number_format($purchaseReturn->total_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
