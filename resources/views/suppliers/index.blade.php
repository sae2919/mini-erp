@extends('layouts.app')
@section('title', 'Suppliers')
@section('heading', 'Suppliers')

@section('header-actions')
    <a href="{{ route('suppliers.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        + Add Supplier
    </a>
@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Supplier name"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Search</button>
        @if(request('search'))
            <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Total Orders</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($suppliers as $supplier)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('suppliers.show', $supplier) }}"
                           class="font-medium text-indigo-600 hover:underline">{{ $supplier->name }}</a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $supplier->phone ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $supplier->email ?? '—' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs bg-blue-50 text-blue-700 rounded-full font-medium">
                            {{ $supplier->purchases_count }} orders
                        </span>
                    </td>
                    <td class="px-4 py-3 flex gap-2">
                        <a href="{{ route('suppliers.edit', $supplier) }}"
                           class="text-xs text-indigo-600 hover:underline">Edit</a>
                        <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}"
                              onsubmit="return confirm('Delete this supplier?')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">
                        No suppliers yet. <a href="{{ route('suppliers.create') }}" class="text-indigo-600">Add one</a>.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $suppliers->links() }}</div>
    </div>
</div>
@endsection
