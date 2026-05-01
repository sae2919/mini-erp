@extends('layouts.app')
@section('title', 'Receivables')
@section('heading', 'Receivables — Unpaid Invoices')

@section('content')
<div class="py-4 space-y-4">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Invoice</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Paid</th>
                    <th class="px-4 py-3">Balance</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($unpaidSales as $sale)
                @php $paid = $sale->payments->sum('amount'); $balance = $sale->total_amount - $paid; @endphp
                <tr class="hover:bg-gray-50 {{ $sale->sale_date->diffInDays() > 30 ? 'bg-red-50' : '' }}">
                    <td class="px-4 py-3 font-medium text-indigo-600">
                        <a href="{{ route('sales.show', $sale) }}">{{ $sale->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $sale->sale_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $sale->customer_display }}</td>
                    <td class="px-4 py-3 font-medium">₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-4 py-3 text-green-600">₹{{ number_format($paid, 2) }}</td>
                    <td class="px-4 py-3 font-bold text-red-600">₹{{ number_format($balance, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs rounded-full
                            {{ $sale->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700' }}">
                            {{ ucfirst($sale->payment_status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('sales.show', $sale) }}"
                           class="text-xs bg-green-600 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition">
                            Collect
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-green-600 font-medium">
                        🎉 All invoices are paid!
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $unpaidSales->links() }}</div>
    </div>
</div>
@endsection
