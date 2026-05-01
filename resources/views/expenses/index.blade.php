@extends('layouts.app')
@section('title', 'Expenses')
@section('heading', 'Expenses')

@section('header-actions')
    <a href="{{ route('expenses.create') }}"
       class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
        + Add Expense
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Category</label>
            <select name="category_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        @if(request()->hasAny(['from','to','category_id']))
            <a href="{{ route('expenses.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    {{-- Summary cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-red-50 border border-red-100 rounded-xl p-4 md:col-span-2">
            <p class="text-xs text-red-600 font-medium">Total Expenses (filtered)</p>
            <p class="text-2xl font-bold text-red-800 mt-1">₹{{ number_format($totalAmount, 2) }}</p>
        </div>
        @foreach($byCategory->take(2) as $cat)
        <div class="bg-white border border-gray-100 rounded-xl p-4">
            <p class="text-xs text-gray-500">{{ $cat->category?->name }}</p>
            <p class="text-xl font-bold text-gray-800 mt-1">₹{{ number_format($cat->total, 2) }}</p>
        </div>
        @endforeach
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Method</th>
                    <th class="px-4 py-3">Amount</th>
                    <th class="px-4 py-3">By</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($expenses as $expense)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600">{{ $expense->expense_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $expense->title }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600">
                            {{ $expense->category->name }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 capitalize">{{ $expense->payment_method }}</td>
                    <td class="px-4 py-3 font-semibold text-red-600">₹{{ number_format($expense->amount, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $expense->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('expenses.destroy', $expense) }}"
                              onsubmit="return confirm('Delete this expense?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                        No expenses recorded. <a href="{{ route('expenses.create') }}" class="text-indigo-600">Add one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $expenses->links() }}</div>
    </div>
</div>
@endsection
