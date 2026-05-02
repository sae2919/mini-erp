@extends('layouts.app')
@section('title','My Dashboard')
@section('heading','My Dashboard — ' . $seller->name)

@section('content')
<div class="py-4 space-y-5">

    {{-- KPIs --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5">
            <p class="text-xs text-indigo-600 font-medium uppercase">My Sales (This Month)</p>
            <p class="text-2xl font-bold text-indigo-800 mt-1">₹{{ number_format($salesThisMonth, 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-5">
            <p class="text-xs text-green-600 font-medium uppercase">Commission Pending</p>
            <p class="text-2xl font-bold text-green-800 mt-1">₹{{ number_format($myCommission, 0) }}</p>
            <p class="text-xs text-green-500 mt-1">Awaiting payout</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-5">
            <p class="text-xs text-orange-600 font-medium uppercase">Balance Due to Company</p>
            <p class="text-2xl font-bold text-orange-800 mt-1">₹{{ number_format(max($myBalanceDue, 0), 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5">
            <p class="text-xs text-blue-600 font-medium uppercase">Total Sales (All Time)</p>
            <p class="text-2xl font-bold text-blue-800 mt-1">₹{{ number_format($mySalesTotal, 0) }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Sales trend chart --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">📈 My Sales — Last 6 Months</h2>
            <div style="position:relative;height:200px">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- My Stock --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">📦 My Current Stock</div>
            @forelse($myStock as $stock)
            <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $stock->product->name }}</p>
                    <p class="text-xs text-gray-400">MRP: ₹{{ number_format($stock->product->mrp, 2) }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-bold rounded-full
                    {{ $stock->quantity <= 5 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                    {{ $stock->quantity }} {{ $stock->product->unit }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-sm text-gray-400 text-center">No stock assigned yet.</p>
            @endforelse
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('seller-pos.index') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                🖥️ POS Terminal
            </a>
            <a href="{{ route('seller-sales.create') }}"
               class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                💰 Record Sale
            </a>
            <a href="{{ route('seller-dispatches.index') }}"
               class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                📦 My Dispatches
            </a>
            <a href="{{ route('seller-sales.index') }}"
               class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition">
                📋 My Sales History
            </a>
        </div>
    </div>

    {{-- Recent Sales --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">💰 Recent Sales</h2>
            <a href="{{ route('seller-sales.create') }}"
               class="bg-green-600 text-white px-4 py-2 rounded-lg text-xs font-medium hover:bg-green-700 transition">
                + Record Sale
            </a>
        </div>
        @forelse($recentSales as $sale)
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
            <div>
                <a href="{{ route('seller-sales.show', $sale) }}"
                   class="text-sm font-medium text-indigo-600 hover:underline">{{ $sale->reference }}</a>
                <p class="text-xs text-gray-400">
                    {{ $sale->customer_name ?: 'Walk-in' }} · {{ $sale->sale_date->format('d M Y') }}
                </p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-green-700">₹{{ number_format($sale->total_amount, 0) }}</p>
                <p class="text-xs text-purple-500">Commission: ₹{{ number_format($sale->commission_amount, 0) }}</p>
            </div>
        </div>
        @empty
        <p class="px-5 py-6 text-sm text-gray-400 text-center">
            No sales yet.
            <a href="{{ route('seller-sales.create') }}" class="text-indigo-600 hover:underline">Record your first sale →</a>
        </p>
        @endforelse
    </div>

    {{-- Recent Dispatches --}}
    @if($recentDispatches->count())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-800">📦 Recent Dispatches from Company</h2>
            <a href="{{ route('seller-dispatches.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
        </div>
        @foreach($recentDispatches as $d)
        <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
            <div>
                <p class="text-sm font-medium text-gray-800">{{ $d->reference }}</p>
                <p class="text-xs text-gray-400">{{ $d->dispatch_date->format('d M Y') }} · {{ $d->items->count() }} products</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-bold text-blue-700">₹{{ number_format($d->total_amount, 0) }}</p>
                <span class="text-xs px-1.5 py-0.5 rounded-full {{ $d->paymentStatusColor() }}">
                    {{ ucfirst($d->payment_status) }}
                </span>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'bar',
    data: {
        labels: @json(collect($monthlySales)->pluck('month')),
        datasets: [{
            label: 'Sales (₹)',
            data: @json(collect($monthlySales)->pluck('sales')),
            backgroundColor: 'rgba(99,102,241,0.75)',
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { ticks: { callback: v => '₹'+(v>=1000?(v/1000).toFixed(0)+'k':v) } }
        }
    }
});
</script>
@endpush