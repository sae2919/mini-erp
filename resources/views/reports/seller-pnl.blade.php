@extends('layouts.app')
@section('title','Seller P&L Report')
@section('heading','Seller P&L Report')

@section('header-actions')
    <a href="{{ url('/export/seller-pnl-excel') . '?' . http_build_query(request()->all()) }}"
   class="btn btn-success">
   Export Excel
</a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Seller</label>
            <select name="seller_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Sellers</option>
                @foreach($sellers as $s)<option value="{{ $s->id }}" {{ request('seller_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Generate</button>
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Seller</th>
                    <th class="px-4 py-3">Region</th>
                    <th class="px-4 py-3">Dispatched</th>
                    <th class="px-4 py-3">Collected</th>
                    <th class="px-4 py-3">Outstanding</th>
                    <th class="px-4 py-3">Sales to Customers</th>
                    <th class="px-4 py-3">Commission Earned</th>
                    <th class="px-4 py-3">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($data as $row)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <a href="{{ route('sellers.show', $row['seller']) }}"
                           class="font-medium text-indigo-600 hover:underline">
                            {{ $row['seller']->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $row['seller']->region ?: '—' }}</td>
                    <td class="px-4 py-3 text-blue-700 font-medium">₹{{ number_format($row['dispatched'], 0) }}</td>
                    <td class="px-4 py-3 text-green-700 font-medium">₹{{ number_format($row['collected'], 0) }}</td>
                    <td class="px-4 py-3 font-semibold {{ $row['outstanding'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($row['outstanding'], 0) }}
                    </td>
                    <td class="px-4 py-3 text-gray-700">₹{{ number_format($row['sales'], 0) }}</td>
                    <td class="px-4 py-3 text-purple-600 font-medium">₹{{ number_format($row['commission'], 0) }}</td>
                    <td class="px-4 py-3 font-bold {{ $row['net'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        ₹{{ number_format(abs($row['net']), 0) }}
                        <span class="text-xs font-normal">{{ $row['net'] > 0 ? 'receivable' : 'clear' }}</span>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                @endforelse
            </tbody>
            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                <tr class="font-bold text-gray-700">
                    <td class="px-4 py-3" colspan="2">Total</td>
                    <td class="px-4 py-3 text-blue-700">₹{{ number_format($data->sum('dispatched'), 0) }}</td>
                    <td class="px-4 py-3 text-green-700">₹{{ number_format($data->sum('collected'), 0) }}</td>
                    <td class="px-4 py-3 text-red-600">₹{{ number_format($data->sum('outstanding'), 0) }}</td>
                    <td class="px-4 py-3">₹{{ number_format($data->sum('sales'), 0) }}</td>
                    <td class="px-4 py-3 text-purple-600">₹{{ number_format($data->sum('commission'), 0) }}</td>
                    <td class="px-4 py-3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection