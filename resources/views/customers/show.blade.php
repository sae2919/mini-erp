@extends('layouts.app')
@section('title', $customer->name)
@section('heading', $customer->name)
@section('header-actions')
    <a href="{{ route('customers.edit', $customer) }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">Edit</a>
    <a href="{{ route('customers.index') }}" class="text-sm text-gray-500 hover:text-gray-700 ml-2">← Back</a>
@endsection
@section('content')
<div class="py-4 space-y-5">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Customer Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Phone</dt><dd>{{ $customer->phone ?? '—' }}</dd>
                <dt class="text-gray-500">Email</dt><dd>{{ $customer->email ?? '—' }}</dd>
                <dt class="text-gray-500">Address</dt><dd>{{ $customer->address ?? '—' }}</dd>
                <dt class="text-gray-500">Notes</dt><dd>{{ $customer->notes ?? '—' }}</dd>
            </dl>
        </div>
        <div class="space-y-3">
            <div class="bg-green-50 border border-green-100 rounded-xl p-5 text-center">
                <p class="text-xs text-green-600 font-medium">Total Spent</p>
                <p class="text-3xl font-bold text-green-800 mt-1">₹{{ number_format($customer->totalSpent(), 2) }}</p>
            </div>
            <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 text-center">
                <p class="text-xs text-indigo-600 font-medium">Total Orders</p>
                <p class="text-3xl font-bold text-indigo-800 mt-1">{{ $customer->totalOrders() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">Purchase History</h2>
            <a href="{{ route('sales.create') }}" class="text-xs text-indigo-600 hover:underline">+ New Sale</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Reference</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Items</th>
                    <th class="px-4 py-2">Total</th>
                    <th class="px-4 py-2"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-indigo-600 font-medium">{{ $sale->reference }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $sale->sale_date->format('d M Y') }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $sale->items->count() }}</td>
                    <td class="px-4 py-2 font-semibold text-green-700">₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-4 py-2">
                        <a href="{{ route('sales.show', $sale) }}" class="text-xs text-indigo-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-5 text-center text-gray-400">No sales yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $sales->links() }}</div>
    </div>
</div>
@endsection
