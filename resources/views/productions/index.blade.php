@extends('layouts.app')
@section('title','Productions')
@section('heading','Production Batches')

@section('header-actions')
    @role('admin|manager|inventory_manager')
    <a href="{{ route('productions.create') }}"
       class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition">
        + New Production
    </a>
    @endrole
@endsection

@section('content')
<div class="py-4 space-y-4">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <p class="text-xs text-blue-600 font-medium uppercase">Total Units Produced</p>
            <p class="text-2xl font-bold text-blue-800 mt-1">{{ number_format($totalUnits) }}</p>
        </div>
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
            <p class="text-xs text-indigo-600 font-medium uppercase">Total Production Cost</p>
            <p class="text-2xl font-bold text-indigo-800 mt-1">₹{{ number_format($totalCost, 2) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-5">
            <p class="text-xs text-green-600 font-medium uppercase">This Month Cost</p>
            <p class="text-2xl font-bold text-green-800 mt-1">₹{{ number_format($thisMonth, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Reference</th>
                    <th class="px-4 py-3">Date</th>
                    <th class="px-4 py-3">Products</th>
                    <th class="px-4 py-3">Total Units</th>
                    <th class="px-4 py-3">Total Cost</th>
                    <th class="px-4 py-3">By</th>
                    <th class="px-4 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($productions as $p)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono font-bold text-blue-600">{{ $p->reference }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $p->production_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $p->items->count() }} product(s)</td>
                    <td class="px-4 py-3 text-gray-600">{{ $p->items->sum('quantity') }} units</td>
                    <td class="px-4 py-3 font-semibold text-blue-700">₹{{ number_format($p->total_cost, 2) }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500">{{ $p->user?->name ?? '—' }}</td>
                    <td class="px-4 py-3 flex gap-2">
                        <a href="{{ route('productions.show',$p) }}" class="text-xs text-indigo-600 hover:underline">View</a>
                        @role('admin')
                        <form method="POST" action="{{ route('productions.destroy',$p) }}" onsubmit="return confirm('Delete? Stock will be reversed.')">
                            @csrf @method('DELETE')
                            <button class="text-xs text-red-500 hover:underline">Delete</button>
                        </form>
                        @endrole
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-gray-400">
                    No productions yet. <a href="{{ route('productions.create') }}" class="text-blue-600">Create first batch →</a>
                </td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $productions->links() }}</div>
    </div>
</div>
@endsection
