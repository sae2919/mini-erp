@extends('layouts.app')
@section('title', 'Purchases')
@section('heading', 'Purchases')

@section('header-actions')
    <a href="{{ route('purchases.create') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        + New Purchase
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Supplier</label>
            <select name="supplier_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}" {{ request('supplier_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        @if(request()->hasAny(['from','to','supplier_id']))
            <a href="{{ route('purchases.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Supplier</th>
                    <th class="px-4 py-3">Items</th>
                    <th class="px-4 py-3">Total</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($purchases as $purchase)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-medium text-indigo-600">
                        <a href="{{ route('purchases.show', $purchase) }}">{{ $purchase->reference }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $purchase->purchase_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $purchase->supplier->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $purchase->items->count() }}</td>
                    <td class="px-4 py-3 font-semibold text-blue-700">₹{{ number_format($purchase->total_amount, 2) }}</td>
                    <td class="px-4 py-3 flex gap-2">
                        <a href="{{ route('purchases.show', $purchase) }}"
                           class="text-xs text-indigo-600 hover:underline">View</a>
                        <form method="POST" action="{{ route('purchases.destroy', $purchase) }}"
                              onsubmit="return confirm('Reverse this purchase? Stock will be decremented.')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:underline">Reverse</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                        No purchases yet. <a href="{{ route('purchases.create') }}" class="text-indigo-600">Create one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $purchases->links() }}</div>
    </div>
</div>
@endsection
