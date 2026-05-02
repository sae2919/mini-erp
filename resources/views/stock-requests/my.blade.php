@extends('layouts.app')

@section('title','My Stock Requests')
@section('heading','My Stock Requests')

@section('content')
<div class="bg-white rounded-xl shadow border border-gray-100 p-6">

    <h2 class="text-lg font-semibold mb-4">📦 My Requests</h2>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th class="py-2">Product</th>
                <th>Quantity</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>

        <tbody>
            @forelse($requests as $r)
            <tr class="border-b">
                <td class="py-2">{{ $r->product->name }}</td>
                <td>{{ $r->quantity }}</td>

                <td>
                    @if($r->status == 'pending')
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>
                    @elseif($r->status == 'approved')
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                    @else
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Rejected</span>
                    @endif
                </td>

                <td>{{ $r->created_at->format('d M Y') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-4 text-gray-400">
                    No requests yet
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

</div>
@endsection