@extends('layouts.app')
@section('title', $purchase->reference)
@section('heading', 'Purchase — ' . $purchase->reference)

@section('header-actions')
    <a href="{{ route('purchases.index') }}"
       class="text-sm text-gray-500 hover:text-gray-700">← Back to Purchases</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 md:col-span-2">
            <h2 class="font-semibold text-gray-800 mb-4">Purchase Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-medium text-gray-800">{{ $purchase->reference }}</dd>

                <dt class="text-gray-500">Supplier</dt>
                {{-- FIX: null-safe in case supplier was soft-deleted after this purchase --}}
                <dd class="text-gray-800">{{ $purchase->supplier?->name ?? '—' }}</dd>

                <dt class="text-gray-500">Date</dt>
                <dd class="text-gray-800">{{ $purchase->purchase_date->format('d M Y') }}</dd>

                <dt class="text-gray-500">Notes</dt>
                <dd class="text-gray-800">{{ $purchase->notes ?: '—' }}</dd>
            </dl>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-center flex flex-col justify-center">
            <p class="text-sm text-blue-600 font-medium">Total Cost</p>
            <p class="text-4xl font-bold text-blue-800 mt-2">
                ₹{{ number_format($purchase->total_amount, 2) }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">
            Line Items
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2">Cost Price</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($purchase->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $item->product?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">
                        {{ $item->product?->sku ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $item->quantity }} {{ $item->product?->unit }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        ₹{{ number_format($item->cost_price, 2) }}
                    </td>
                    <td class="px-4 py-3 font-semibold text-blue-700">
                        ₹{{ number_format($item->subtotal ?? ($item->cost_price * $item->quantity), 2) }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="4" class="px-4 py-3 text-right font-bold text-gray-700">Total</td>
                    <td class="px-4 py-3 font-bold text-blue-800 text-base">
                        ₹{{ number_format($purchase->total_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{--
        FIX: Reverse button was visible to everyone who could view this page
        (admin AND inventory_manager). inventory_manager hitting Reverse got
        a 403 with no explanation. Admin only.
    --}}
    @if(auth()->user()->hasRole('admin'))
    <div class="flex justify-end">
        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
              onsubmit="return confirm('Reverse purchase {{ $purchase->reference }}? Stock will be decremented back.')">
            @csrf @method('DELETE')
            <button type="submit"
                    class="px-4 py-2 text-sm bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition">
                🔄 Reverse Purchase
            </button>
        </form>
    </div>
    @endif

</div>
@endsection