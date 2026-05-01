@extends('layouts.app')
@section('title', $purchase->reference)
@section('heading', 'Purchase — ' . $purchase->reference)

@section('header-actions')
    @role('admin|inventory_manager')
    <a href="{{ route('purchase-returns.create', $purchase) }}"
       class="bg-orange-100 text-orange-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-200 transition">
        ↩️ Return to Supplier
    </a>
    @endrole
    <a href="{{ route('purchases.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    {{-- ── Purchase Details ────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Purchase Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-bold text-indigo-600">{{ $purchase->reference }}</dd>

                <dt class="text-gray-500">Supplier</dt>
                <dd>
                    @if($purchase->supplier)
                        <a href="{{ route('suppliers.show', $purchase->supplier) }}"
                           class="text-indigo-600 hover:underline">
                            {{ $purchase->supplier->name }}
                        </a>
                    @else
                        <span class="text-gray-400">—</span>
                    @endif
                </dd>

                <dt class="text-gray-500">Purchase Date</dt>
                <dd class="text-gray-800">{{ $purchase->purchase_date->format('d M Y') }}</dd>

                <dt class="text-gray-500">Notes</dt>
                <dd class="text-gray-800">{{ $purchase->notes ?: '—' }}</dd>
            </dl>
        </div>

        <div class="bg-blue-50 border border-blue-100 rounded-xl p-6 text-center flex flex-col justify-center">
            <p class="text-sm text-blue-600 font-medium">Total Value</p>
            <p class="text-4xl font-bold text-blue-800 mt-2">
                ₹{{ number_format($purchase->total_amount, 2) }}
            </p>
            <p class="text-xs text-blue-500 mt-2">
                {{ $purchase->items->sum('quantity') }} units purchased
            </p>
        </div>
    </div>

    {{-- ── Line Items ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="font-semibold text-gray-800">Line Items</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">SKU</th>
                    <th class="px-4 py-3">Quantity</th>
                    <th class="px-4 py-3">Unit Price</th>
                    <th class="px-4 py-3">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($purchase->items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">
                        <a href="{{ route('products.show', $item->product) }}"
                           class="hover:text-indigo-600">
                            {{ $item->product->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->product->sku }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }} {{ $item->product->unit }}</td>
                    <td class="px-4 py-3 text-gray-600">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-blue-700">
                        ₹{{ number_format($item->quantity * $item->unit_price, 2) }}
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

    {{-- ── Purchase Returns for this purchase ──────────────────── --}}
    @php
        $purchaseReturns = \App\Models\PurchaseReturn::where('purchase_id', $purchase->id)
            ->with('items.product')
            ->latest()->get();
    @endphp

    @if($purchaseReturns->count())
    <div class="bg-white rounded-xl border border-orange-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-orange-100 flex items-center justify-between bg-orange-50">
            <h2 class="font-semibold text-orange-800">↩️ Returns for this Purchase</h2>
            <span class="text-xs text-orange-600 font-medium">
                Total returned: ₹{{ number_format($purchaseReturns->sum('total_amount'), 2) }}
            </span>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Reference</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Reason</th>
                    <th class="px-4 py-2">Amount</th>
                    <th class="px-4 py-2">Status</th>
                    <th class="px-4 py-2">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($purchaseReturns as $return)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 font-mono text-orange-600 font-medium">{{ $return->reference }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $return->return_date->format('d M Y') }}</td>
                    <td class="px-4 py-2 text-gray-600">
                        {{ \App\Models\PurchaseReturn::reasons()[$return->reason] ?? $return->reason }}
                    </td>
                    <td class="px-4 py-2 font-semibold text-orange-600">
                        ₹{{ number_format($return->total_amount, 2) }}
                    </td>
                    <td class="px-4 py-2">
                        <span class="px-2 py-0.5 text-xs rounded-full font-semibold {{ $return->statusColor() }}">
                            {{ ucfirst($return->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-2">
                        <a href="{{ route('purchase-returns.show', $return) }}"
                           class="text-xs text-indigo-600 hover:underline">View</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    {{-- ── Delete purchase ─────────────────────────────────────── --}}
    @role('admin')
    <div class="flex justify-end">
        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
              onsubmit="return confirm('Delete this purchase? Stock will be reversed.')">
            @csrf @method('DELETE')
            <button class="px-4 py-2 text-sm bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition">
                ❌ Delete Purchase
            </button>
        </form>
    </div>
    @endrole

</div>
@endsection
