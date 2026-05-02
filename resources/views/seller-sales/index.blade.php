@extends('layouts.app')
@section('title','Seller Sales')
@section('heading','Seller Sales')

@section('header-actions')
    @role('seller')
    <a href="{{ route('seller-sales.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
        + Record Sale
    </a>
    @endrole
    @role('admin|sales_executive')
    <a href="{{ route('seller-sales.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
        + Record Sale
    </a>
    @endrole
@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        @if(!$mySeller)
        <div>
            <label class="block text-xs text-gray-500 mb-1">Seller</label>
            <select name="seller_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Sellers</option>
                @foreach($sellers as $s)<option value="{{ $s->id }}" {{ request('seller_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        @endif
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        @if(request()->hasAny(['seller_id','from','to']))<a href="{{ route('seller-sales.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>@endif
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    @if(!$mySeller)<th class="px-4 py-3">Seller</th>@endif
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Sale Amount</th>
                    <th class="px-4 py-3">Commission</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sales as $sale)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono font-bold text-green-600">
                        <a href="{{ route('seller-sales.show',$sale) }}" class="hover:underline">{{ $sale->reference }}</a>
                    </td>
                    @if(!$mySeller)<td class="px-4 py-3 text-gray-600">{{ $sale->seller->name }}</td>@endif
                    <td class="px-4 py-3 text-gray-600">{{ $sale->customer_name ?: 'Walk-in' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $sale->sale_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-purple-600">₹{{ number_format($sale->commission_amount, 2) }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('seller-sales.show',$sale) }}" class="text-xs text-indigo-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">No sales recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $sales->links() }}</div>
    </div>
</div>
@endsection
