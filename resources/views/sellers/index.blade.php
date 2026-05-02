@extends('layouts.app')
@section('title','Sellers')
@section('heading','Sellers / Dealers')

@section('header-actions')
    @role('admin|sales_executive')
    <a href="{{ route('sellers.create') }}"
       class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
        + Add Seller
    </a>
    @endrole
@endsection

@section('content')
<div class="py-4 space-y-4">
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or region"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-56">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Search</button>
        @if(request('search'))<a href="{{ route('sellers.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>@endif
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Region</th>
                    <th class="px-4 py-3">Phone</th>
                    <th class="px-4 py-3">Total Sales</th>
                    <th class="px-4 py-3">Balance Due</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sellers as $s)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('sellers.show',$s) }}" class="font-medium text-indigo-600 hover:underline">{{ $s->name }}</a>
                        @if($s->user)<span class="ml-1 text-xs text-green-500">● Login</span>@endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->region ?: '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $s->phone ?: '—' }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">₹{{ number_format($s->total_sales ?? 0, 0) }}</td>
                    <td class="px-4 py-3 font-semibold {{ $s->balance_due > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($s->balance_due, 0) }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $s->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                            {{ $s->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('sellers.show',$s) }}" class="text-xs text-indigo-600 hover:underline mr-2">View</a>
                        @role('admin|sales_executive')
                        <a href="{{ route('sellers.edit',$s) }}" class="text-xs text-gray-500 hover:underline">Edit</a>
                        @endrole
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">
                    No sellers yet. <a href="{{ route('sellers.create') }}" class="text-indigo-600">Add one →</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $sellers->links() }}</div>
    </div>
</div>
@endsection
