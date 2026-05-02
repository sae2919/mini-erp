@extends('layouts.app')
@section('title','My Dispatches')
@section('heading','My Dispatch History')

@section('content')
<div class="py-4 space-y-4">

    @php $seller = \App\Models\Seller::where('user_id', auth()->id())->first(); @endphp

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Total Received</p>
            <p class="text-xl font-bold text-blue-800 mt-1">₹{{ number_format($seller?->dispatchOrders()->sum('total_amount') ?? 0, 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Total Paid</p>
            <p class="text-xl font-bold text-green-800 mt-1">₹{{ number_format($seller?->payments()->sum('amount') ?? 0, 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
            <p class="text-xs text-orange-600 font-medium">Balance Due</p>
            <p class="text-xl font-bold text-orange-800 mt-1">₹{{ number_format(max($seller?->balance_due ?? 0, 0), 0) }}</p>
        </div>
        <div class="bg-gray-50 border border-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-600 font-medium">Total Orders</p>
            <p class="text-xl font-bold text-gray-800 mt-1">{{ $seller?->dispatchOrders()->count() ?? 0 }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Dispatch Orders</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Products</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">Paid</th>
                    <th class="px-4 py-3">Balance</th>
                    <th class="px-4 py-3">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($dispatches as $d)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-mono font-bold text-indigo-600">{{ $d->reference }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $d->dispatch_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $d->items->count() }} items</td>
                    <td class="px-4 py-3 font-semibold text-blue-700">₹{{ number_format($d->total_amount, 2) }}</td>
                    <td class="px-4 py-3 text-green-600">₹{{ number_format($d->paid_amount, 2) }}</td>
                    <td class="px-4 py-3 font-semibold {{ $d->balanceDue() > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($d->balanceDue(), 2) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $d->paymentStatusColor() }}">
                            {{ ucfirst($d->payment_status) }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No dispatches yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $dispatches->links() }}</div>
    </div>
</div>
@endsection
