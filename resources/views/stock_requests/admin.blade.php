@extends('layouts.app')

@section('content')
<div class="p-6">

    <h2 class="text-xl font-bold mb-4">📦 Stock Requests (Admin)</h2>

    {{-- 🔥 SUMMARY CARDS --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">

        {{-- TOTAL --}}
        <div class="bg-blue-100 border border-blue-200 rounded-xl p-5">
            <p class="text-sm text-blue-700 font-medium">Total Value</p>

            <h3 class="text-3xl font-bold text-blue-900 mt-2">
                ₹{{ number_format(
                    $requests
                        ->where('status','approved')
                        ->sum(fn($r) => ($r->product->price ?? 0) * $r->quantity),
                    2
                ) }}
            </h3>
        </div>

        {{-- PAID --}}
        <div class="bg-green-100 border border-green-200 rounded-xl p-5">
            <p class="text-sm text-green-700 font-medium">Paid</p>

            <h3 class="text-3xl font-bold text-green-800 mt-2">
                ₹{{ number_format(
                    $requests
                        ->where('payment_status','paid')
                        ->sum(fn($r) => ($r->product->price ?? 0) * $r->quantity),
                    2
                ) }}
            </h3>
        </div>

        {{-- PENDING --}}
        <div class="bg-red-100 border border-red-200 rounded-xl p-5">
            <p class="text-sm text-red-700 font-medium">Pending</p>

            <h3 class="text-3xl font-bold text-red-700 mt-2">
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

                    <td class="p-3">
                        {{ $req->seller->name ?? 'N/A' }}
                    </td>

                    <td class="p-3">
                        {{ $req->product->name ?? 'N/A' }}
                    </td>

                    <td class="p-3">
                        {{ $req->quantity }}
                    </td>

                    <td class="p-3">
                        ₹{{ number_format($total, 2) }}
                    </td>

                    <td class="p-3">

                        @if($status == 'pending')

                            <span class="px-2 py-1 bg-yellow-200 rounded">
                                Pending
                            </span>

                        @elseif($status == 'approved')

                            <span class="px-2 py-1 bg-green-200 rounded">
                                Approved
                            </span>

                        @else

                            <span class="px-2 py-1 bg-red-200 rounded">
                                Rejected
                            </span>

                        @endif

                    </td>

                    <td class="p-3">

                        @if($status === 'rejected')

                            <span class="px-2 py-1 bg-gray-200 text-gray-600 rounded">
                                N/A
                            </span>

                        @elseif($req->payment_status == 'paid')

                            <span class="px-2 py-1 bg-green-200 rounded">
                                Paid
                            </span>

                        @else

                            <span class="px-2 py-1 bg-red-200 rounded">
                                Pending
                            </span>

                        @endif

                    </td>

                    <td class="p-3 flex gap-2">

                        @if(strtolower($req->status) === 'pending')

                            <form method="POST" action="{{ route('stock-requests.approve',$req->id) }}">
                                @csrf

                                <button
                                    type="submit"
                                    style="background:green;color:white;padding:6px 10px;border-radius:5px;"
                                >
                                    Approve
                                </button>
                            </form>

                            <form method="POST" action="{{ route('stock-requests.reject',$req->id) }}">
                                @csrf

                                <button
                                    type="submit"
                                    style="background:red;color:white;padding:6px 10px;border-radius:5px;"
                                >
                                    Reject
                                </button>
                            </form>

                        @else

                            <span style="color:gray;">
                                Done
                            </span>

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

        </table>


        {{-- PROFESSIONAL PAGINATION --}}
        @if ($requests->hasPages())

        <div
            class="flex items-center justify-between px-5 py-4 border-t border-gray-100 bg-white flex-wrap gap-4"
        >

            {{-- RESULTS --}}
            <div class="text-xs font-bold uppercase tracking-widest text-gray-400">
                Showing
                <span class="text-gray-700">{{ $requests->firstItem() }}</span>
                to
                <span class="text-gray-700">{{ $requests->lastItem() }}</span>
                of
                <span class="text-gray-700">{{ $requests->total() }}</span>
                results
            </div>


            {{-- PAGINATION --}}
            <div class="flex items-center gap-3 flex-wrap">

                {{-- PREVIOUS --}}
                @if($requests->onFirstPage())

                    <span class="w-11 h-11 rounded-2xl bg-gray-100 text-gray-300 flex items-center justify-center border border-gray-200 cursor-not-allowed text-lg">
                        ‹
                    </span>

                @else

                    <a
                        href="{{ $requests->previousPageUrl() }}"
                        class="w-11 h-11 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition text-lg"
                    >
                        ‹
                    </a>

                @endif


                {{-- PAGE INFO --}}
                <div
                    class="px-7 h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-sm font-bold uppercase tracking-wide shadow-md"
                >
                    Page {{ $requests->currentPage() }} / {{ $requests->lastPage() }}
                </div>


                {{-- NEXT --}}
                @if($requests->hasMorePages())

                    <a
                        href="{{ $requests->nextPageUrl() }}"
                        class="w-11 h-11 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition text-lg"
                    >
                        ›
                    </a>

                @else

                    <span class="w-11 h-11 rounded-2xl bg-gray-100 text-gray-300 flex items-center justify-center border border-gray-200 cursor-not-allowed text-lg">
                        ›
                    </span>

                @endif


                {{-- PAGE JUMP --}}
                <form
                    method="GET"
                    action="{{ url()->current() }}"
                    class="flex items-center gap-2"
                >

                    <input
                        type="number"
                        name="page"
                        min="1"
                        max="{{ $requests->lastPage() }}"
                        placeholder="Page"
                        class="w-24 h-11 rounded-2xl border border-gray-200 px-4 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none"
                    >

                    <button
                        type="submit"
                        class="px-5 h-11 rounded-2xl bg-gray-900 hover:bg-black text-white text-sm font-bold transition"
                    >
                        GO
                    </button>

                </form>

            </div>

        </div>

        @endif

    </div>

</div>
@endsection