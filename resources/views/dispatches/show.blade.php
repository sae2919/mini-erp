@extends('layouts.app')
@section('title',$dispatch->reference)
@section('heading','Dispatch — '.$dispatch->reference)
@section('header-actions')
    <a href="{{ route('dispatches.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Dispatch Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-bold text-indigo-600">{{ $dispatch->reference }}</dd>
                <dt class="text-gray-500">Seller</dt>
                <dd><a href="{{ route('sellers.show',$dispatch->seller) }}" class="text-indigo-600 hover:underline">{{ $dispatch->seller->name }}</a></dd>
                <dt class="text-gray-500">Date</dt>
                <dd>{{ $dispatch->dispatch_date->format('d M Y') }}</dd>
                <dt class="text-gray-500">Created By</dt>
                <dd>{{ $dispatch->user?->name ?? '—' }}</dd>
                <dt class="text-gray-500">Status</dt>
                <dd><span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $dispatch->statusColor() }}">{{ ucfirst($dispatch->status) }}</span></dd>
                <dt class="text-gray-500">Notes</dt>
                <dd>{{ $dispatch->notes ?: '—' }}</dd>
            </dl>
        </div>
        <div class="space-y-3">
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 text-center">
                <p class="text-sm text-indigo-600 font-medium">Total Amount</p>
                <p class="text-3xl font-bold text-indigo-800 mt-1">₹{{ number_format($dispatch->total_amount, 2) }}</p>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 font-medium mb-1">Payment Status</p>
                <span class="px-3 py-1 text-sm font-bold rounded-full {{ $dispatch->paymentStatusColor() }}">
                    {{ ucfirst($dispatch->payment_status) }}
                </span>
                @if($dispatch->balanceDue() > 0)
                <p class="text-sm font-bold text-red-600 mt-2">Balance: ₹{{ number_format($dispatch->balanceDue(), 2) }}</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Items --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Products Dispatched</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2">Dispatch Price</th>
                    <th class="px-4 py-2">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($dispatch->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $item->quantity }} {{ $item->product->unit }}</td>
                    <td class="px-4 py-3 text-gray-600">₹{{ number_format($item->dispatch_price, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-indigo-700">₹{{ number_format($item->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="3" class="px-4 py-3 text-right font-bold">Total</td>
                    <td class="px-4 py-3 font-bold text-indigo-800 text-base">₹{{ number_format($dispatch->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Payment History + Record Payment --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">💳 Payment History</div>
            @forelse($dispatch->payments as $payment)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ \App\Models\SellerPayment::methods()[$payment->method] ?? $payment->method }}</p>
                    <p class="text-xs text-gray-400">{{ $payment->paid_at->format('d M Y') }}{{ $payment->reference?' · '.$payment->reference:'' }}</p>
                </div>
                <span class="font-bold text-green-700">₹{{ number_format($payment->amount, 2) }}</span>
            </div>
            @empty
            <p class="px-5 py-4 text-sm text-gray-400">No payments yet.</p>
            @endforelse
        </div>

        @if($dispatch->balanceDue() > 0.01)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Record Payment</div>
            <form method="POST" action="{{ route('dispatches.payment',$dispatch) }}" class="p-5 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Amount (₹) *</label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                               max="{{ $dispatch->balanceDue() }}" value="{{ number_format($dispatch->balanceDue(),2,'.','')}}"
                               required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Date *</label>
                        <input type="date" name="paid_at" value="{{ date('Y-m-d') }}" required
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Method *</label>
                    <select name="method" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(\App\Models\SellerPayment::methods() as $k=>$v)
                        <option value="{{ $k }}">{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Reference</label>
                    <input type="text" name="reference" placeholder="UPI ID / Cheque no."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-green-600 text-white rounded-lg text-sm font-bold hover:bg-green-700 transition">
                    💳 Record Payment
                </button>
            </form>
        </div>
        @else
        <div class="bg-green-50 border border-green-100 rounded-xl flex items-center justify-center">
            <div class="text-center p-8">
                <p class="text-4xl mb-2">✅</p>
                <p class="font-bold text-green-700">Fully Paid</p>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
