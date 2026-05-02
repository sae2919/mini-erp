@extends('layouts.app')
@section('title',$seller->name)
@section('heading',$seller->name)

@section('header-actions')
    @role('admin|sales_executive')
    <a href="{{ route('dispatches.create', ['seller_id'=>$seller->id]) }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        📤 New Dispatch
    </a>
    <a href="{{ route('sellers.edit',$seller) }}"
       class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
        Edit
    </a>
    @endrole
    <a href="{{ route('sellers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Total Dispatched</p>
            <p class="text-xl font-bold text-blue-800 mt-1">₹{{ number_format($totalDispatched, 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Total Collected</p>
            <p class="text-xl font-bold text-green-800 mt-1">₹{{ number_format($totalPaid, 0) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
            <p class="text-xs text-orange-600 font-medium">Balance Due</p>
            <p class="text-xl font-bold text-orange-800 mt-1">₹{{ number_format(max($seller->balance_due,0), 0) }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">
            <p class="text-xs text-purple-600 font-medium">Commission Earned</p>
            <p class="text-xl font-bold text-purple-800 mt-1">₹{{ number_format($pendingComm, 0) }}</p>
            <p class="text-xs text-purple-400">Pending payout</p>
        </div>
    </div>

    {{-- Seller Info + Stock --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-3">Details</h2>
            <dl class="grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                <dt class="text-gray-500">Region</dt><dd>{{ $seller->region ?: '—' }}</dd>
                <dt class="text-gray-500">Phone</dt><dd>{{ $seller->phone ?: '—' }}</dd>
                <dt class="text-gray-500">Email</dt><dd>{{ $seller->email ?: '—' }}</dd>
                <dt class="text-gray-500">Credit Limit</dt><dd>₹{{ number_format($seller->credit_limit, 0) }}</dd>
                <dt class="text-gray-500">Login</dt>
                <dd>
                    @if($seller->user)
                        <span class="text-green-600 font-medium">✅ {{ $seller->user->email }}</span>
                    @else
                        <span class="text-gray-400">No login</span>
                    @endif
                </dd>
                <dt class="text-gray-500">Status</dt>
                <dd><span class="px-2 py-0.5 text-xs rounded-full {{ $seller->is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">{{ $seller->is_active ? 'Active' : 'Inactive' }}</span></dd>
            </dl>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">📦 Current Stock</div>
            @forelse($seller->stocks as $stock)
            <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $stock->product->name }}</p>
                    <p class="text-xs text-gray-400">MRP: ₹{{ number_format($stock->product->mrp, 2) }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-bold rounded-full {{ $stock->quantity <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                    {{ $stock->quantity }} {{ $stock->product->unit }}
                </span>
            </div>
            @empty
            <p class="px-5 py-4 text-sm text-gray-400">No stock dispatched yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Recent Dispatches --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">📤 Recent Dispatches</h2>
            <a href="{{ route('dispatches.index', ['seller_id'=>$seller->id]) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        @forelse($seller->dispatchOrders as $d)
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
            <div>
                <a href="{{ route('dispatches.show',$d) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ $d->reference }}</a>
                <p class="text-xs text-gray-400">{{ $d->dispatch_date->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold">₹{{ number_format($d->total_amount, 0) }}</p>
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $d->paymentStatusColor() }}">{{ ucfirst($d->payment_status) }}</span>
            </div>
        </div>
        @empty
        <p class="px-5 py-4 text-sm text-gray-400">No dispatches yet.</p>
        @endforelse
    </div>

    {{-- Recent Sales --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">💰 Recent Seller Sales</h2>
            <a href="{{ route('seller-sales.index', ['seller_id'=>$seller->id]) }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        @forelse($seller->sales as $sale)
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
            <div>
                <a href="{{ route('seller-sales.show',$sale) }}" class="text-sm font-medium text-indigo-600 hover:underline">{{ $sale->reference }}</a>
                <p class="text-xs text-gray-400">{{ $sale->customer_name ?: 'Walk-in' }} · {{ $sale->sale_date->format('d M Y') }}</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-green-700">₹{{ number_format($sale->total_amount, 0) }}</p>
                <p class="text-xs text-purple-500">Comm: ₹{{ number_format($sale->commission_amount, 0) }}</p>
            </div>
        </div>
        @empty
        <p class="px-5 py-4 text-sm text-gray-400">No sales recorded yet.</p>
        @endforelse
    </div>
</div>
@endsection
