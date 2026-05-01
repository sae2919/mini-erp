@extends('layouts.app')
@section('title', 'Payments')
@section('heading', 'Payments')

@section('header-actions')
    <a href="{{ route('payments.receivables') }}"
       class="bg-orange-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-700 transition">
        🧾 Receivables
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Total Collected</p>
            <p class="text-2xl font-bold text-green-800 mt-1">₹{{ number_format($totalCollected, 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Today's Collection</p>
            <p class="text-2xl font-bold text-blue-800 mt-1">₹{{ number_format($todayCollected, 0) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
            <p class="text-xs text-yellow-600 font-medium">Unpaid Invoices</p>
            <p class="text-2xl font-bold text-yellow-800 mt-1">{{ $unpaidSales }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-600 font-medium">Overdue (30+ days)</p>
            <p class="text-2xl font-bold text-red-800 mt-1">₹{{ number_format(max($overdueAmount, 0), 0) }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Method</label>
            <select name="method" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Methods</option>
                @foreach(\App\Models\Payment::methods() as $key => $label)
                    <option value="{{ $key }}" {{ request('method') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        @if(request()->hasAny(['method','from','to']))
            <a href="{{ route('payments.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Method</th>
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">By</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($payments as $payment)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-gray-600">{{ $payment->paid_at->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-medium text-indigo-600">
                        <a href="{{ route('sales.show', $payment->sale) }}">{{ $payment->sale->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $payment->sale->customer_display }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs bg-blue-50 text-blue-700 rounded-full">
                            {{ \App\Models\Payment::methods()[$payment->method] ?? $payment->method }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs font-mono">{{ $payment->reference ?: '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">₹{{ number_format($payment->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $payment->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                              onsubmit="return confirm('Delete this payment?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">No payments recorded yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $payments->links() }}</div>
    </div>
</div>
@endsection
