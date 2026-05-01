@extends('layouts.app')
@section('title', 'Supplier Dashboard')
@section('heading', 'Supplier Dashboard')

@section('header-actions')
    <a href="{{ route('suppliers.index') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        Manage Suppliers
    </a>
@endsection

@section('content')
<div class="py-4 space-y-6">

    {{-- ── KPI Cards ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Suppliers</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalSuppliers }}</p>
            <p class="text-xs text-green-600 mt-1">{{ $activeSuppliers }} active (with orders)</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Spent</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($totalSpent, 0) }}</p>
            <p class="text-xs text-blue-600 mt-1">₹{{ number_format($thisMonthSpent, 0) }} this month</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Purchase Orders</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalOrders }}</p>
            <p class="text-xs text-indigo-600 mt-1">Avg ₹{{ number_format($avgOrderValue, 0) }} per order</p>
        </div>
    </div>

    {{-- ── Charts Row ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Monthly Purchase Spend --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">📦 Monthly Purchase Spend (Last 12 Months)</h2>
            <div style="position:relative;height:240px">
                <canvas id="monthlySpendChart"
                        role="img"
                        aria-label="Bar chart showing monthly purchase spend over the last 12 months">
                </canvas>
            </div>
        </div>

        {{-- Top Suppliers by Spend --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">🏆 Top Suppliers by Total Spend</h2>
            <div style="position:relative;height:240px">
                <canvas id="topSuppliersChart"
                        role="img"
                        aria-label="Horizontal bar chart showing top suppliers by total spend">
                </canvas>
            </div>
        </div>
    </div>

    {{-- ── Top Suppliers Table + Recent Purchases ────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top Suppliers Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">🏭 Top Suppliers</h2>
                <a href="{{ route('suppliers.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500 font-medium">
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Supplier</th>
                        <th class="px-4 py-2">Orders</th>
                        <th class="px-4 py-2">Total Spent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($topSuppliers as $i => $supplier)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center
                                {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : ($i === 1 ? 'bg-gray-100 text-gray-600' : ($i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500')) }}">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('suppliers.show', $supplier) }}"
                               class="font-medium text-indigo-600 hover:underline">{{ $supplier->name }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $supplier->purchases_count }}</td>
                        <td class="px-4 py-3 font-semibold text-blue-700">
                            ₹{{ number_format($supplier->purchases_sum_total_amount ?? 0, 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No purchases yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Purchases --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">🛒 Recent Purchases</h2>
                <a href="{{ route('purchases.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentPurchases as $purchase)
                <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $purchase->supplier->name }}</p>
                        <p class="text-xs text-gray-400">
                            {{ $purchase->reference }} · {{ $purchase->purchase_date->format('d M Y') }}
                        </p>
                    </div>
                    <span class="text-sm font-semibold text-blue-700">
                        ₹{{ number_format($purchase->total_amount, 0) }}
                    </span>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400">No purchases yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- ── Quick Stats ─────────────────────────────────────────────── --}}
    @if($topThisMonth && $topThisMonth->purchases_count > 0)
    <div class="bg-indigo-50 border border-indigo-100 rounded-xl p-5 flex items-center gap-4">
        <span class="text-3xl">🏆</span>
        <div>
            <p class="text-sm font-semibold text-indigo-800">Most Active Supplier This Month</p>
            <p class="text-lg font-bold text-indigo-900">{{ $topThisMonth->name }}</p>
            <p class="text-xs text-indigo-600">{{ $topThisMonth->purchases_count }} purchase order(s) this month</p>
        </div>
        <div class="ml-auto">
            <a href="{{ route('suppliers.show', $topThisMonth) }}"
               class="text-sm bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">
                View Supplier
            </a>
        </div>
    </div>
    @endif

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const gridColor = 'rgba(107,114,128,0.1)';

// ── Monthly Spend Chart ────────────────────────────────────────
new Chart(document.getElementById('monthlySpendChart'), {
    type: 'bar',
    data: {
        labels: @json($monthlyLabels),
        datasets: [{
            label: 'Spend (₹)',
            data: @json($monthlyData),
            backgroundColor: 'rgba(99,102,241,0.75)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false }, ticks: { maxRotation: 45, font: { size: 10 } } },
            y: {
                grid: { color: gridColor },
                ticks: { callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v), font: { size: 10 } }
            }
        }
    }
});

// ── Top Suppliers Chart ───────────────────────────────────────
new Chart(document.getElementById('topSuppliersChart'), {
    type: 'bar',
    data: {
        labels: @json($topSupplierLabels),
        datasets: [{
            label: 'Total Spend (₹)',
            data: @json($topSupplierData),
            backgroundColor: [
                'rgba(99,102,241,0.8)',
                'rgba(34,197,94,0.8)',
                'rgba(251,146,60,0.8)',
                'rgba(236,72,153,0.8)',
                'rgba(14,165,233,0.8)',
                'rgba(168,85,247,0.8)',
            ],
            borderRadius: 4,
        }]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                grid: { color: gridColor },
                ticks: { callback: v => '₹' + (v >= 1000 ? (v/1000).toFixed(0)+'k' : v), font: { size: 10 } }
            },
            y: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>
@endpush
