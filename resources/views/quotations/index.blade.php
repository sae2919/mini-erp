@extends('layouts.app')
@section('title', 'Quotations')
@section('heading', 'Quotations')

@section('header-actions')
    <a href="{{ route('quotations.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        + New Quotation
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                @foreach(['draft','sent','accepted','rejected','expired','converted'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
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
        @if(request()->hasAny(['status','from','to']))
            <a href="{{ route('quotations.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Valid Until</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($quotations as $q)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-indigo-600">
                        <a href="{{ route('quotations.show', $q) }}">{{ $q->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $q->customer?->name ?? $q->customer_name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $q->quotation_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 {{ $q->valid_until->isPast() && !in_array($q->status,['converted','accepted']) ? 'text-red-500' : 'text-gray-600' }}">
                        {{ $q->valid_until->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 font-semibold">₹{{ number_format($q->total_amount, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $q->statusColor() }}">
                            {{ ucfirst($q->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 flex gap-2">
                        <a href="{{ route('quotations.show', $q) }}" class="text-xs text-indigo-600 hover:underline">View</a>
                        @if(!in_array($q->status, ['converted', 'rejected']))
                        <form method="POST" action="{{ route('quotations.convert', $q) }}">
                            @csrf
                            <button class="text-xs text-green-600 hover:underline">Convert</button>
                        </form>
                        @endif
                        @if(in_array($q->status, ['draft']))
                        <form method="POST" action="{{ route('quotations.destroy', $q) }}"
                              onsubmit="return confirm('Delete this quotation?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        No quotations yet. <a href="{{ route('quotations.create') }}" class="text-indigo-600">Create one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $quotations->links() }}</div>
    </div>
</div>
@endsection
