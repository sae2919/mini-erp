@extends('layouts.app')

@section('content')
<div class="p-6">

    <h2 class="text-xl font-bold mb-4">📦 Stock Requests (Admin)</h2>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-3">Seller</th>
                    <th class="p-3">Product</th>
                    <th class="p-3">Qty</th>
                    <th class="p-3">Status</th>
                     <th class="p-3">Payment</th>
                    <th class="p-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                @php
                    $status = strtolower(trim($req->status));
                @endphp

                <tr class="border-t">
                    <td class="p-3">{{ $req->seller->name ?? 'N/A' }}</td>
                    <td class="p-3">{{ $req->product->name ?? 'N/A' }}</td>
                    <td class="p-3">{{ $req->quantity }}</td>

                    {{-- STATUS --}}
                    <td class="p-3">
                        @if($status == 'pending')
                            <span class="px-2 py-1 bg-yellow-200 rounded">Pending</span>
                        @elseif($status == 'approved')
                            <span class="px-2 py-1 bg-green-200 rounded">Approved</span>
                        @else
                            <span class="px-2 py-1 bg-red-200 rounded">Rejected</span>
                        @endif
                    </td>
                    <td class="p-3">
    @if($req->payment_status == 'paid')
        <span class="px-2 py-1 bg-green-200 rounded">Paid</span>
    @else
        <span class="px-2 py-1 bg-red-200 rounded">Pending</span>
    @endif
</td>

                    {{-- ACTION --}}
                    <td class="p-3 flex gap-2">

    @if(strtolower($req->status) === 'pending')

        <form method="POST" action="{{ route('stock-requests.approve',$req->id) }}">
            @csrf
            <button type="submit" style="background:green;color:white;padding:6px 10px;border-radius:5px;">
                Approve
            </button>
        </form>

        <form method="POST" action="{{ route('stock-requests.reject',$req->id) }}">
            @csrf
            <button type="submit" style="background:red;color:white;padding:6px 10px;border-radius:5px;">
                Reject
            </button>
        </form>

    @else
        <span style="color:gray;">Done</span>
    @endif

</td>
                </tr>
                @endforeach

                @if($requests->isEmpty())
                <tr>
                    <td colspan="5" class="text-center p-4 text-gray-500">
                        No stock requests found
                    </td>
                </tr>
                @endif

            </tbody>
        </table>
    </div>

</div>
@endsection