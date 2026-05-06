@extends('layouts.app')

@section('title','My Stock Requests')
@section('heading','My Stock Requests')

@section('content')
<div class="bg-white rounded-xl shadow border border-gray-100 p-6">

    <h2 class="text-lg font-semibold mb-4">📦 My Requests</h2>

    {{-- 🔥 SUMMARY CARDS --}}
    <div class="grid grid-cols-3 gap-4 mb-6">

        <div class="bg-blue-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Total Requested</p>
            <h2 class="text-xl font-bold text-blue-600">
                ₹{{ number_format($totalAmount ?? 0, 2) }}
            </h2>
        </div>

        <div class="bg-green-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Total Paid</p>
            <h2 class="text-xl font-bold text-green-600">
                ₹{{ number_format($paidAmount ?? 0, 2) }}
            </h2>
        </div>

        <div class="bg-red-50 p-4 rounded-lg">
            <p class="text-sm text-gray-500">Pending Amount</p>
            <h2 class="text-xl font-bold text-red-600">
                ₹{{ number_format($pendingAmount ?? 0, 2) }}
            </h2>
        </div>

    </div>

    <table class="w-full text-sm">
        <thead>
            <tr class="text-left text-gray-500 border-b">
                <th>Product</th>
                <th>Cost (₹)</th>
                <th>Quantity</th>
                <th>Total (₹)</th>
                <th>Status</th>
                <th>Date</th>
                <th>Payment</th>
            </tr>
        </thead>

        <tbody>
            @forelse($requests as $req)
            <tr class="border-b">

                {{-- Product --}}
                <td class="py-2">{{ $req->product->name }}</td>

                {{-- Cost --}}
                <td>
                    ₹{{ number_format($req->product->price ?? 0, 2) }}
                </td>

                {{-- Quantity --}}
                <td>{{ $req->quantity }}</td>

                {{-- Total --}}
                <td>
                    ₹{{ number_format(($req->product->price ?? 0) * $req->quantity, 2) }}
                </td>

                {{-- Status --}}
                <td>
                    @if($req->status == 'pending')
                        <span class="px-2 py-1 text-xs bg-yellow-100 text-yellow-700 rounded">Pending</span>
                    @elseif($req->status == 'approved')
                        <span class="px-2 py-1 text-xs bg-green-100 text-green-700 rounded">Approved</span>
                    @else
                        <span class="px-2 py-1 text-xs bg-red-100 text-red-700 rounded">Rejected</span>
                    @endif
                </td>

                {{-- Date --}}
                <td>{{ $req->created_at->format('d M Y') }}</td>

                {{-- Payment --}}
                <td>
                    @if($req->status === 'rejected')
                        <span class="text-xs text-gray-500 font-semibold">N/A</span>

                    @elseif($req->status === 'approved' && $req->payment_status === 'pending')
                        <form method="POST" action="{{ route('stock-requests.pay', $req->id) }}"
                              class="flex items-center gap-2">
                            @csrf
                            <select name="payment_method"
                                    class="text-sm border border-gray-300 rounded px-2 py-1">
                                <option value="online">Online</option>
                                <option value="offline">Offline</option>
                            </select>
                            <button type="submit"
                                    class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                                Pay
                            </button>
                        </form>

                    @elseif($req->payment_status === 'paid')
                        <span class="text-xs text-green-600 font-semibold">
                            ✅ Paid ({{ ucfirst($req->payment_method) }})
                        </span>

                    @else
                        <span class="text-xs text-gray-400">—</span>
                    @endif
                </td>

            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center py-4 text-gray-400">
                    No requests yet
                </td>
            </tr>
            @endforelse

            {{-- 🔥 GRAND TOTAL ROW --}}
            @if(isset($totalAmount) && $requests->count() > 0)
            <tr class="bg-gray-100 font-semibold">
                <td colspan="3" class="text-right py-2">Grand Total:</td>
                <td class="text-blue-600">
                    ₹{{ number_format($totalAmount, 2) }}
                </td>
                <td colspan="3"></td>
            </tr>
            @endif

        </tbody>
    </table>

</div>
@endsection