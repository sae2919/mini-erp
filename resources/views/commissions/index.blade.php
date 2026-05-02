@extends('layouts.app')
@section('title','Commissions')
@section('heading','Commission Payouts')

@section('header-actions')
    <form method="POST" action="{{ route('commissions.payout-all') }}">
        @csrf
        <button onclick="return confirm('Mark ALL pending commissions as paid?')"
                class="bg-green-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-700 transition">
            💰 Pay All Pending
        </button>
    </form>
@endsection

@section('content')
<div class="py-4 space-y-4">

    {{-- KPIs --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-5">
            <p class="text-xs text-yellow-600 font-medium uppercase">Pending Payout</p>
            <p class="text-2xl font-bold text-yellow-800 mt-1">₹{{ number_format($totalPending, 2) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-5">
            <p class="text-xs text-green-600 font-medium uppercase">Total Paid</p>
            <p class="text-2xl font-bold text-green-800 mt-1">₹{{ number_format($totalPaid, 2) }}</p>
        </div>
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
            <p class="text-xs text-indigo-600 font-medium uppercase">Total Earned (All Time)</p>
            <p class="text-2xl font-bold text-indigo-800 mt-1">₹{{ number_format($totalAll, 2) }}</p>
        </div>
    </div>

    {{-- Payout per seller --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Pay by Seller</h2>
        <div class="flex flex-wrap gap-3">
            @foreach($sellers as $s)
            @php $pending = $s->commissions()->where('status','pending')->sum('amount'); @endphp
            @if($pending > 0)
            <form method="POST" action="{{ route('commissions.payout-seller') }}" class="inline">
                @csrf
                <input type="hidden" name="seller_id" value="{{ $s->id }}">
                <button class="px-4 py-2 bg-indigo-600 text-white text-xs rounded-lg hover:bg-indigo-700 transition">
                    Pay {{ $s->name }} — ₹{{ number_format($pending, 0) }}
                </button>
            </form>
            @endif
            @endforeach
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Seller</label>
            <select name="seller_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Sellers</option>
                @foreach($sellers as $s)<option value="{{ $s->id }}" {{ request('seller_id')==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Status</label>
            <select name="status" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All</option>
                <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                <option value="paid"    {{ request('status')=='paid'   ?'selected':'' }}>Paid</option>
            </select>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Filter</button>
        @if(request()->hasAny(['seller_id','status']))<a href="{{ route('commissions.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>@endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Sale Reference</th>
                    <th class="px-4 py-3">Seller</th>
                    <th class="px-4 py-3">Sale Date</th>
                    <th class="px-4 py-3">Sale Amount</th>
                    <th class="px-4 py-3">Commission</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Paid On</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($commissions as $c)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono text-indigo-600">
                        <a href="{{ route('seller-sales.show', $c->sellerSale) }}" class="hover:underline">
                            {{ $c->sellerSale->reference }}
                        </a>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $c->seller->name }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $c->sellerSale->sale_date->format('d M Y') }}</td>
                    <td class="px-4 py-3 text-gray-600">₹{{ number_format($c->sellerSale->total_amount, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-purple-700">₹{{ number_format($c->amount, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium
                            {{ $c->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                            {{ ucfirst($c->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">{{ $c->paid_at?->format('d M Y') ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($c->status === 'pending')
                        <form method="POST" action="{{ route('commissions.mark-paid', $c) }}">
                            @csrf
                            <button class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded hover:bg-green-200 transition">
                                Mark Paid
                            </button>
                        </form>
                        @else
                        <span class="text-xs text-gray-400">✅ Done</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">No commissions found.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $commissions->links() }}</div>
    </div>
</div>
@endsection
