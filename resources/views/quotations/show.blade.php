@extends('layouts.app')
@section('title', $quotation->reference)
@section('heading', 'Quotation — ' . $quotation->reference)

@section('header-actions')
    @if(!in_array($quotation->status, ['converted','rejected']))
    <form method="POST" action="{{ route('quotations.convert', $quotation) }}" class="inline">
        @csrf
        <button class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
            ✅ Convert to Sale
        </button>
    </form>
    @endif
    <a href="{{ route('quotations.index') }}" class="text-sm text-gray-500 hover:text-gray-700 ml-2">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Quotation Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-medium">{{ $quotation->reference }}</dd>
                <dt class="text-gray-500">Customer</dt>
                <dd>{{ $quotation->customer?->name ?? $quotation->customer_name }}</dd>
                <dt class="text-gray-500">Email</dt>
                <dd>{{ $quotation->customer_email ?: '—' }}</dd>
                <dt class="text-gray-500">Phone</dt>
                <dd>{{ $quotation->customer_phone ?: '—' }}</dd>
                <dt class="text-gray-500">Date</dt>
                <dd>{{ $quotation->quotation_date->format('d M Y') }}</dd>
                <dt class="text-gray-500">Valid Until</dt>
                <dd class="{{ $quotation->valid_until->isPast() && !in_array($quotation->status,['converted','accepted']) ? 'text-red-500 font-medium' : '' }}">
                    {{ $quotation->valid_until->format('d M Y') }}
                    @if($quotation->valid_until->isPast() && !in_array($quotation->status,['converted','accepted']))
                        (Expired)
                    @endif
                </dd>
                <dt class="text-gray-500">Notes</dt>
                <dd>{{ $quotation->notes ?: '—' }}</dd>
            </dl>
        </div>

        <div class="space-y-4">
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 text-center">
                <p class="text-sm text-indigo-600 font-medium">Total Amount</p>
                <p class="text-3xl font-bold text-indigo-800 mt-1">₹{{ number_format($quotation->total_amount, 2) }}</p>
            </div>

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Update Status</p>
                @if(!in_array($quotation->status, ['converted']))
                <form method="POST" action="{{ route('quotations.status', $quotation) }}" class="space-y-2">
                    @csrf
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(['draft','sent','accepted','rejected'] as $s)
                            <option value="{{ $s }}" {{ $quotation->status === $s ? 'selected' : '' }}>
                                {{ ucfirst($s) }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="w-full py-2 text-sm bg-gray-700 text-white rounded-lg hover:bg-gray-800 transition">
                        Update
                    </button>
                </form>
                @else
                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $quotation->statusColor() }}">
                    Converted to Sale
                </span>
                @if($quotation->convertedSale)
                    <a href="{{ route('sales.show', $quotation->convertedSale) }}"
                       class="block text-xs text-indigo-600 hover:underline mt-2">
                        View Sale → {{ $quotation->convertedSale->reference }}
                    </a>
                @endif
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Line Items</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2">Unit Price</th>
                    <th class="px-4 py-2">Discount</th>
                    <th class="px-4 py-2">Tax</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($quotation->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-gray-600">₹{{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->discount }}%</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->tax_rate }}%</td>
                    <td class="px-4 py-3 font-semibold text-indigo-700">₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total</td>
                    <td class="px-4 py-3 font-bold text-indigo-800 text-base">
                        ₹{{ number_format($quotation->total_amount, 2) }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
