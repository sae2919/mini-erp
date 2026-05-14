@extends('layouts.app')

@section('title','Dispatch Orders')
@section('heading','Dispatch Orders')

@section('header-actions')
    @role('admin|sales_executive')
    <a href="{{ route('dispatches.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        + New Dispatch
    </a>
    @endrole
@endsection

@section('content')
<div class="py-4 space-y-4">

    {{-- SUMMARY CARDS --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- TOTAL DISPATCHED --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <p class="text-xs text-blue-600 font-medium uppercase">
                Total Dispatched Value
            </p>

            <p class="text-3xl font-bold text-blue-800 mt-2">
                ₹{{ number_format($totalValue, 0) }}
            </p>
        </div>

        {{-- TOTAL PENDING --}}
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-5">
            <p class="text-xs text-orange-600 font-medium uppercase">
                Total Pending Collection
            </p>

            <p class="text-3xl font-bold text-orange-800 mt-2">
                ₹{{ number_format($totalPending, 0) }}
            </p>
        </div>

    </div>


    {{-- FILTER --}}
    <form
        method="GET"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end"
    >

        {{-- SELLER --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">
                Seller
            </label>

            <select
                name="seller_id"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
                <option value="">
                    All Sellers
                </option>

                @foreach($sellers as $s)

                    <option
                        value="{{ $s->id }}"
                        {{ request('seller_id') == $s->id ? 'selected' : '' }}
                    >
                        {{ $s->name }}
                    </option>

                @endforeach

            </select>
        </div>


        {{-- PAYMENT --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">
                Payment
            </label>

            <select
                name="payment_status"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
                <option value="">
                    All
                </option>

                <option
                    value="unpaid"
                    {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}
                >
                    Unpaid
                </option>

                <option
                    value="partial"
                    {{ request('payment_status') == 'partial' ? 'selected' : '' }}
                >
                    Partial
                </option>

                <option
                    value="paid"
                    {{ request('payment_status') == 'paid' ? 'selected' : '' }}
                >
                    Paid
                </option>

            </select>
        </div>


        {{-- FROM DATE --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">
                From
            </label>

            <input
                type="date"
                id="fromDate"
                name="from"
                value="{{ request('from') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
        </div>


        {{-- TO DATE --}}
        <div>
            <label class="block text-xs text-gray-500 mb-1">
                To
            </label>

            <input
                type="date"
                id="toDate"
                name="to"
                value="{{ request('to') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
        </div>


        {{-- FILTER BUTTON --}}
        <button
            class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition"
        >
            Filter
        </button>


        {{-- CLEAR --}}
        @if(request()->hasAny(['seller_id','payment_status','from','to']))

            <a
                href="{{ route('dispatches.index') }}"
                class="text-sm text-gray-500 hover:underline"
            >
                Clear
            </a>

        @endif

    </form>


    {{-- TABLE --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        <table class="w-full text-sm">

            <thead class="bg-gray-50 border-b border-gray-100">

                <tr class="text-left text-gray-500 font-medium">

                    <th class="px-4 py-3">
                        Reference
                    </th>

                    <th class="px-4 py-3">
                        Seller
                    </th>

                    <th class="px-4 py-3">
                        Date
                    </th>

                    <th class="px-4 py-3">
                        Items
                    </th>

                    <th class="px-4 py-3">
                        Amount
                    </th>

                    <th class="px-4 py-3">
                        Balance
                    </th>

                    <th class="px-4 py-3">
                        Payment
                    </th>

                    <th class="px-4 py-3">
                        Actions
                    </th>

                </tr>

            </thead>


            <tbody class="divide-y divide-gray-50">

                @forelse($dispatches as $d)

                <tr class="hover:bg-gray-50 transition">

                    {{-- REFERENCE --}}
                    <td class="px-4 py-3 font-mono font-bold text-indigo-600">
                        <a
                            href="{{ route('dispatches.show',$d) }}"
                            class="hover:underline"
                        >
                            {{ $d->reference }}
                        </a>
                    </td>


                    {{-- SELLER --}}
                    <td class="px-4 py-3 text-gray-800 font-medium">
                        {{ $d->seller->name }}
                    </td>


                    {{-- DATE --}}
                    <td class="px-4 py-3 text-gray-600">
                        {{ $d->dispatch_date->format('d M Y') }}
                    </td>


                    {{-- ITEMS --}}
                    <td class="px-4 py-3 text-gray-600">
                        {{ $d->items->count() }} products
                    </td>


                    {{-- AMOUNT --}}
                    <td class="px-4 py-3 font-semibold text-blue-700">
                        ₹{{ number_format($d->total_amount, 0) }}
                    </td>


                    {{-- BALANCE --}}
                    <td class="px-4 py-3 font-semibold {{ $d->balanceDue() > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($d->balanceDue(), 0) }}
                    </td>


                    {{-- PAYMENT --}}
                    <td class="px-4 py-3">

                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $d->paymentStatusColor() }}">
                            {{ ucfirst($d->payment_status) }}
                        </span>

                    </td>


                    {{-- ACTION --}}
                    <td class="px-4 py-3">
                        <a
                            href="{{ route('dispatches.show',$d) }}"
                            class="text-xs text-indigo-600 hover:underline"
                        >
                            View
                        </a>
                    </td>

                </tr>

                @empty

                <tr>

                    <td colspan="8" class="px-4 py-8 text-center text-gray-400">
                        No dispatch orders yet.

                        <a
                            href="{{ route('dispatches.create') }}"
                            class="text-indigo-600"
                        >
                            Create one →
                        </a>
                    </td>

                </tr>

                @endforelse

            </tbody>

        </table>


        {{-- PROFESSIONAL PAGINATION --}}
        @if ($dispatches->hasPages())

        <div
            class="flex items-center justify-between px-5 py-4 border-t border-gray-100 bg-white flex-wrap gap-4"
        >

            {{-- RESULTS --}}
            <div class="text-xs font-bold uppercase tracking-widest text-gray-400">
                Showing
                <span class="text-gray-700">{{ $dispatches->firstItem() }}</span>
                to
                <span class="text-gray-700">{{ $dispatches->lastItem() }}</span>
                of
                <span class="text-gray-700">{{ $dispatches->total() }}</span>
                results
            </div>


            {{-- PAGINATION --}}
            <div class="flex items-center gap-3 flex-wrap">

                {{-- PREVIOUS --}}
                @if($dispatches->onFirstPage())

                    <span class="w-11 h-11 rounded-2xl bg-gray-100 text-gray-300 flex items-center justify-center border border-gray-200 cursor-not-allowed text-lg">
                        ‹
                    </span>

                @else

                    <a
                        href="{{ $dispatches->previousPageUrl() }}"
                        class="w-11 h-11 rounded-2xl bg-gray-100 hover:bg-gray-200 text-gray-700 flex items-center justify-center transition text-lg"
                    >
                        ‹
                    </a>

                @endif


                {{-- PAGE INFO --}}
                <div
                    class="px-7 h-11 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-sm font-bold uppercase tracking-wide shadow-md"
                >
                    Page {{ $dispatches->currentPage() }} / {{ $dispatches->lastPage() }}
                </div>


                {{-- NEXT --}}
                @if($dispatches->hasMorePages())

                    <a
                        href="{{ $dispatches->nextPageUrl() }}"
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
                        max="{{ $dispatches->lastPage() }}"
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


{{-- DATE VALIDATION --}}
<script>

document.addEventListener('DOMContentLoaded', function () {

    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');

    function updateToDateLimit() {

        if (fromDate.value) {

            toDate.min = fromDate.value;

            if (toDate.value && toDate.value < fromDate.value) {

                toDate.value = fromDate.value;

            }

        } else {

            toDate.removeAttribute('min');

        }

    }

    updateToDateLimit();

    fromDate.addEventListener('change', updateToDateLimit);

});

</script>

@endsection