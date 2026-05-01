@extends('layouts.app')
@section('title', 'Dashboard')
@section('heading', 'Dashboard')

@section('content')
<div class="py-4 space-y-5">

    {{-- ── Row 1: Core KPIs with growth indicators ────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase tracking-wide font-medium">Sales This Month</span>
                @if($salesGrowth >= 0)
                    <span class="text-xs font-semibold text-green-600 bg-green-50 px-2 py-0.5 rounded-full">↑ {{ $salesGrowth }}%</span>
                @else
                    <span class="text-xs font-semibold text-red-600 bg-red-50 px-2 py-0.5 rounded-full">↓ {{ abs($salesGrowth) }}%</span>
                @endif
            </div>
            <p class="text-2xl font-bold text-gray-900">₹{{ number_format($salesThisMonth, 0) }}</p>
            <p class="text-xs text-gray-400 mt-1">Last month: ₹{{ number_format($salesLastMonth, 0) }}</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-2">Gross Profit</p>
            <p class="text-2xl font-bold {{ $grossProfit >= 0 ? 'text-green-700' : 'text-red-600' }}">
                ₹{{ number_format($grossProfit, 0) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">Margin: {{ $profitMargin }}%</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-2">Net Profit</p>
            <p class="text-2xl font-bold {{ $netProfit >= 0 ? 'text-indigo-700' : 'text-red-600' }}">
                ₹{{ number_format($netProfit, 0) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">After ₹{{ number_format($expensesThisMonth, 0) }} expenses</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-2">Outstanding</p>
            <p class="text-2xl font-bold {{ $outstandingAmount > 0 ? 'text-orange-600' : 'text-gray-400' }}">
                ₹{{ number_format(max($outstandingAmount, 0), 0) }}
            </p>
            <p class="text-xs text-gray-400 mt-1">
                <a href="{{ route('payments.receivables') }}" class="text-indigo-500 hover:underline">View receivables →</a>
            </p>
        </div>
    </div>

    {{-- ── Row 2: Secondary KPIs ────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-4">
            <p class="text-xs text-indigo-600 font-medium">Today's Sales</p>
            <p class="text-xl font-bold text-indigo-800 mt-1">₹{{ number_format($salesToday, 0) }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Purchases This Month</p>
            <p class="text-xl font-bold text-blue-800 mt-1">₹{{ number_format($purchasesThisMonth, 0) }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-100 rounded-xl p-4">
            <p class="text-xs text-yellow-600 font-medium">Pending Orders</p>
            <p class="text-xl font-bold text-yellow-800 mt-1">{{ $pendingOrders }}</p>
        </div>
        <div class="bg-red-50 border border-red-100 rounded-xl p-4">
            <p class="text-xs text-red-600 font-medium">Low Stock Items</p>
            <p class="text-xl font-bold text-red-800 mt-1">{{ $lowStockCount }}</p>
        </div>
    </div>

    {{-- ── Row 3: Charts ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">📈 Sales Trend — Last 30 Days</h2>
            <div style="position:relative;height:220px">
                <canvas id="salesTrendChart" role="img" aria-label="30-day sales trend"></canvas>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">📊 Revenue vs Expenses — 12 Months</h2>
            <div style="position:relative;height:220px">
                <canvas id="revenueExpenseChart" role="img" aria-label="Revenue vs expenses 12 months"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Row 4: Widgets ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Top Products --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">🏆 Top Products</h2>
            <div style="position:relative;height:200px">
                <canvas id="topProductsChart" role="img" aria-label="Top products by revenue"></canvas>
            </div>
        </div>

        {{-- Low Stock --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">⚠️ Low Stock</h2>
                <a href="{{ route('products.index', ['stock_status' => 'low']) }}"
                   class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($lowStockProducts->take(5) as $p)
                <div class="flex items-center justify-between px-5 py-2.5">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $p->name }}</p>
                        <p class="text-xs text-gray-400">{{ $p->sku }}</p>
                    </div>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full
                        {{ $p->stock_quantity == 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $p->stock_quantity }} left
                    </span>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400">All products well stocked ✅</p>
                @endforelse
            </div>
        </div>

        {{-- Notifications + Quick Actions --}}
        <div class="space-y-4">
            @if($unreadNotifications->count())
            <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
                <div class="flex items-center justify-between px-5 py-3 border-b border-gray-100">
                    <h2 class="font-semibold text-gray-800">🔔 Notifications</h2>
                    <a href="{{ route('notifications.index') }}" class="text-xs text-indigo-600 hover:underline">All</a>
                </div>
                @foreach($unreadNotifications as $notif)
                <a href="{{ route('notifications.read', $notif) }}"
                   class="flex items-start gap-3 px-4 py-3 hover:bg-gray-50 border-b border-gray-50 last:border-0">
                    <span class="text-base mt-0.5">{{ $notif->icon }}</span>
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $notif->title }}</p>
                        <p class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </a>
                @endforeach
            </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-4">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Quick Actions</p>
                <div class="grid grid-cols-2 gap-2">
                    @role('admin|sales_executive')
                    <a href="{{ route('sales.create') }}"
                       class="text-xs bg-green-600 text-white px-3 py-2 rounded-lg hover:bg-green-700 text-center transition">
                        💰 New Sale
                    </a>
                    <a href="{{ route('quotations.create') }}"
                       class="text-xs bg-indigo-600 text-white px-3 py-2 rounded-lg hover:bg-indigo-700 text-center transition">
                        📋 Quotation
                    </a>
                    @endrole
                    @role('admin|inventory_manager')
                    <a href="{{ route('purchases.create') }}"
                       class="text-xs bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 text-center transition">
                        🛒 Purchase
                    </a>
                    @endrole
                    <a href="{{ route('reports.profit') }}"
                       class="text-xs bg-gray-700 text-white px-3 py-2 rounded-lg hover:bg-gray-800 text-center transition">
                        💹 P&L Report
                    </a>
                    @role('admin')
                    <a href="{{ route('payments.receivables') }}"
                       class="text-xs bg-orange-600 text-white px-3 py-2 rounded-lg hover:bg-orange-700 text-center transition col-span-2">
                        💳 Receivables
                    </a>
                    @endrole
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const gridColor = 'rgba(107,114,128,0.1)';
Chart.defaults.font.size = 11;

new Chart(document.getElementById('salesTrendChart'), {
    type: 'line',
    data: {
        labels: @json(collect($last30)->pluck('date')),
        datasets: [{
            label: 'Sales (₹)',
            data: @json(collect($last30)->pluck('total')),
            borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.08)',
            borderWidth: 2, pointRadius: 2, tension: 0.4, fill: true,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { maxTicksLimit: 8, maxRotation: 0 } },
            y: { grid: { color: gridColor }, ticks: { callback: v => '₹' + (v>=1000?(v/1000).toFixed(0)+'k':v) } }
        }
    }
});

new Chart(document.getElementById('revenueExpenseChart'), {
    type: 'bar',
    data: {
        labels: @json(collect($last12Months)->pluck('month')),
        datasets: [
            { label: 'Revenue', data: @json(collect($last12Months)->pluck('revenue')), backgroundColor: 'rgba(34,197,94,0.75)', borderRadius: 4 },
            { label: 'Expenses', data: @json(collect($last12Months)->pluck('expenses')), backgroundColor: 'rgba(239,68,68,0.65)', borderRadius: 4 },
            { label: 'Purchases', data: @json(collect($last12Months)->pluck('purchases')), backgroundColor: 'rgba(99,102,241,0.55)', borderRadius: 4 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 10, padding: 10 } } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: gridColor }, ticks: { callback: v => '₹'+(v>=1000?(v/1000).toFixed(0)+'k':v) } }
        }
    }
});

new Chart(document.getElementById('topProductsChart'), {
    type: 'bar',
    data: {
        labels: @json($topProducts->pluck('name')),
        datasets: [{
            label: 'Revenue (₹)',
            data: @json($topProducts->pluck('revenue')),
            backgroundColor: ['rgba(99,102,241,0.8)','rgba(34,197,94,0.8)','rgba(251,146,60,0.8)','rgba(236,72,153,0.8)','rgba(14,165,233,0.8)','rgba(168,85,247,0.8)'],
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: gridColor }, ticks: { callback: v => '₹'+(v>=1000?(v/1000).toFixed(0)+'k':v) } },
            y: { grid: { display: false } }
        }
    }
});
</script>
@endpush
