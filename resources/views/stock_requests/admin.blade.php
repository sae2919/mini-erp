@extends('layouts.app')

@section('content')
<div class="p-6">

    <h2 class="text-xl font-bold mb-4">📦 Stock Requests (Admin)</h2>

    {{-- 🔥 SUMMARY CARDS --}}
    <div class="grid grid-cols-3 gap-4 mb-4">
        <div class="bg-blue-100 p-4 rounded">
            <p class="text-sm">Total Value</p>
            <h3 class="text-xl font-bold">
                ₹{{ number_format(
                    $requests
                        ->where('status','approved')
                        ->sum(fn($r) => ($r->product->price ?? 0) * $r->quantity),
                    2
                ) }}
            </h3>
        </div>

        <div class="bg-green-100 p-4 rounded">
            <p class="text-sm">Paid</p>
            <h3 class="text-xl font-bold text-green-700">
                ₹{{ number_format($requests->where('payment_status','paid')->sum(fn($r) => ($r->product->price ?? 0) * $r->quantity), 2) }}
            </h3>
        </div>

        <div class="bg-red-100 p-4 rounded">
            <p class="text-sm">Pending</p>
            <h3 class="text-xl font-bold text-red-700">
                ₹{{ number_format(
                    $requests
                        ->where('status','approved')
                        ->where('payment_status','pending')
                        ->sum(fn($r) => ($r->product->price ?? 0) * $r->quantity),
                    2
                ) }}
            </h3>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-100 text-left">
                <tr>
                    <th class="p-3">Seller</th>
                    <th class="p-3">Product</th>
                    <th class="p-3">Qty</th>
                    <th class="p-3">Total (₹)</th>
                    <th class="p-3">Status</th>
                    <th class="p-3">Payment</th>
                    <th class="p-3">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $req)
                @php
                    $status = strtolower(trim($req->status));
                    $total = ($req->product->price ?? 0) * $req->quantity;
                @endphp

                <tr class="border-t">
                    <td class="p-3">{{ $req->seller->name ?? 'N/A' }}</td>
                    <td class="p-3">{{ $req->product->name ?? 'N/A' }}</td>
                    <td class="p-3">{{ $req->quantity }}</td>

                    <td class="p-3">
                        ₹{{ number_format($total, 2) }}
                    </td>

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
                        @if($status === 'rejected')
                            <span class="px-2 py-1 bg-gray-200 text-gray-600 rounded">N/A</span>
                        @elseif($req->payment_status == 'paid')
                            <span class="px-2 py-1 bg-green-200 rounded">Paid</span>
                        @else
                            <span class="px-2 py-1 bg-red-200 rounded">Pending</span>
                        @endif
                    </td>

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
                    <td colspan="7" class="text-center p-4 text-gray-500">
                        No stock requests found
                    </td>
                </tr>
                @endif

            </tbody>
            {{ $requests->links() }}
        </table>
    </div>

</div>
@endsection