@extends('layouts.app')
@section('title',$sellerSale->reference)
@section('heading','Sale — '.$sellerSale->reference)
@section('header-actions')
    <a href="{{ route('seller-sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Sale Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-bold text-green-600">{{ $sellerSale->reference }}</dd>
                <dt class="text-gray-500">Seller</dt>
                <dd><a href="{{ route('sellers.show',$sellerSale->seller) }}" class="text-indigo-600 hover:underline">{{ $sellerSale->seller->name }}</a></dd>
                <dt class="text-gray-500">Customer</dt>
                <dd>{{ $sellerSale->customer_name ?: 'Walk-in' }}</dd>
                <dt class="text-gray-500">Customer Phone</dt>
                <dd>{{ $sellerSale->customer_phone ?: '—' }}</dd>
                <dt class="text-gray-500">Date</dt>
                <dd>{{ $sellerSale->sale_date->format('d M Y') }}</dd>
                <dt class="text-gray-500">Notes</dt>
                <dd>{{ $sellerSale->notes ?: '—' }}</dd>
            </dl>
        </div>

        <div class="space-y-3">
            <div class="bg-green-50 border border-green-100 rounded-xl p-5 text-center">
                <p class="text-sm text-green-600 font-medium">Total Sale</p>
                <p class="text-3xl font-bold text-green-800 mt-1">₹{{ number_format($sellerSale->total_amount, 2) }}</p>
            </div>
            <div class="bg-purple-50 border border-purple-100 rounded-xl p-4 text-center">
                <p class="text-xs text-purple-600 font-medium">Seller Commission</p>
                <p class="text-2xl font-bold text-purple-800 mt-1">₹{{ number_format($sellerSale->commission_amount, 2) }}</p>
                <span class="text-xs px-2 py-0.5 rounded-full {{ $sellerSale->commission?->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ ucfirst($sellerSale->commission?->status ?? 'pending') }}
                </span>
            </div>
            <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                <p class="text-xs text-blue-600 font-medium">Company Receivable</p>
                <p class="text-xl font-bold text-blue-800 mt-1">₹{{ number_format($sellerSale->company_amount, 2) }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Items Sold</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2">Selling Price</th>
                    <th class="px-4 py-2">Dispatch Price</th>
                    <th class="px-4 py-2">Commission</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($sellerSale->items as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-green-700 font-medium">₹{{ number_format($item->selling_price, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">₹{{ number_format($item->dispatch_price, 2) }}</td>
                    <td class="px-4 py-3 text-purple-600">{{ $item->commission_rate }}% = ₹{{ number_format($item->commission_amount, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total</td>
                    <td class="px-4 py-3 font-bold text-green-800 text-base">₹{{ number_format($sellerSale->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
