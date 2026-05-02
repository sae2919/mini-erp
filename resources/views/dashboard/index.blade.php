@extends('layouts.app')
@section('title','Dashboard')
@section('heading','Dashboard')

@section('content')
<div class="py-4 space-y-5">

    {{-- ── KPI Row 1 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Warehouse Stock</p>
            <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($warehouseStock) }} units</p>
            @if($lowStockCount > 0)
            <p class="text-xs text-red-500 mt-1">⚠️ {{ $lowStockCount }} products low</p>
            @else
            <p class="text-xs text-green-500 mt-1">✅ All stocked well</p>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Dispatched</p>
            <p class="text-2xl font-bold text-blue-700 mt-1">₹{{ number_format($totalDispatched, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">This month: ₹{{ number_format($dispatchThisMonth, 0) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Outstanding Balance</p>
            <p class="text-2xl font-bold text-orange-600 mt-1">₹{{ number_format($outstandingBalance, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">Pending from sellers</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Commissions Due</p>
            <p class="text-2xl font-bold text-purple-600 mt-1">₹{{ number_format($pendingCommissions, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">Total: ₹{{ number_format($totalCommissions, 0) }}</p>
        </div>
    </div>

    {{-- ── KPI Row 2 ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            <p class="text-xs text-indigo-600 font-medium">Seller Sales (Month)</p>
            <p class="text-xl font-bold text-indigo-800 mt-1">₹{{ number_format($salesThisMonth, 0) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Collected (Total)</p>
            <p class="text-xl font-bold text-green-800 mt-1">₹{{ number_format($totalCollected, 0) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
            <p class="text-xs text-yellow-600 font-medium">Production Cost (Month)</p>
            <p class="text-xl font-bold text-yellow-800 mt-1">₹{{ number_format($productionThisMonth, 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Total Products</p>
            <p class="text-xl font-bold text-blue-800 mt-1">{{ $totalProducts }}</p>
        </div>
    </div>

    {{-- ── Charts ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">📊 Monthly — Dispatched vs Collected vs Sold</h2>
            <div style="position:relative;height:220px">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">🏆 Top Sellers</div>
            @forelse($topSellers as $s)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50 last:border-0">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $s->name }}</p>
                    <p class="text-xs text-gray-400">{{ $s->sales_count }} sales</p>
                </div>
                <span class="text-sm font-bold text-indigo-700">
                    ₹{{ number_format($s->total_sales ?? 0, 0) }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-sm text-gray-400 text-center">No sales yet.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Low Stock + Recent Dispatches ──────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">⚠️ Low Stock Products</h2>
                <a href="{{ route('products.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            @forelse($lowStockProducts as $p)
            <div class="flex items-center justify-between px-5 py-2.5 border-b border-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $p->name }}</p>
                    <p class="text-xs text-gray-400">{{ $p->category?->name }}</p>
                </div>
                <span class="px-2 py-1 text-xs font-bold rounded-full
                    {{ $p->stock_quantity == 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $p->stock_quantity }} {{ $p->unit }}
                </span>
            </div>
            @empty
            <p class="px-5 py-6 text-sm text-gray-400 text-center">All products well stocked ✅</p>
            @endforelse
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">📤 Recent Dispatches</h2>
                <a href="{{ route('dispatches.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            @forelse($recentDispatches as $d)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $d->reference }}</p>
                    <p class="text-xs text-gray-400">{{ $d->seller->name }} · {{ $d->dispatch_date->format('d M') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm font-bold text-blue-700">₹{{ number_format($d->total_amount, 0) }}</p>
                    <span class="text-xs px-1.5 py-0.5 rounded-full {{ $d->paymentStatusColor() }}">
                        {{ ucfirst($d->payment_status) }}
                    </span>
                </div>
            </div>
            @empty
            <p class="px-5 py-6 text-sm text-gray-400 text-center">No dispatches yet.</p>
            @endforelse
        </div>
    </div>

    {{-- ── Quick Actions ───────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</p>
        <div class="flex flex-wrap gap-3">
            @role('admin|manager|inventory_manager')
            <a href="{{ route('productions.create') }}"
               class="px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition">
                🏭 New Production
            </a>
            @endrole
            @role('admin|sales_executive')
            <a href="{{ route('dispatches.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition">
                📤 New Dispatch
            </a>
            <a href="{{ route('sellers.create') }}"
               class="px-4 py-2 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition">
                🏪 Add Seller
            </a>
            @endrole
            <a href="{{ route('products.index') }}"
               class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 transition">
                📦 Products
            </a>
            <a href="{{ route('seller-sales.index') }}"
               class="px-4 py-2 bg-orange-600 text-white text-sm rounded-lg hover:bg-orange-700 transition">
                💰 Seller Sales
            </a>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: @json(collect($monthlyData)->pluck('month')),
        datasets: [
            { label: 'Dispatched', data: @json(collect($monthlyData)->pluck('dispatched')), backgroundColor: 'rgba(99,102,241,0.75)', borderRadius: 4 },
            { label: 'Collected',  data: @json(collect($monthlyData)->pluck('collected')),  backgroundColor: 'rgba(34,197,94,0.75)',  borderRadius: 4 },
            { label: 'Sold',       data: @json(collect($monthlyData)->pluck('sold')),       backgroundColor: 'rgba(251,146,60,0.75)', borderRadius: 4 },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 10 } } },
        scales: {
            x: { grid: { display: false } },
            y: { ticks: { callback: v => '₹'+(v>=1000?(v/1000).toFixed(0)+'k':v) } }
        }
    }
});
</script>
@endpush