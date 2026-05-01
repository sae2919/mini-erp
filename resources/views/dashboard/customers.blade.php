@extends('layouts.app')
@section('title', 'Customer Dashboard')
@section('heading', 'Customer Dashboard')

@section('header-actions')
    <a href="{{ route('customers.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        + Add Customer
    </a>
    <a href="{{ route('customers.index') }}"
       class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition ml-2">
        Manage Customers
    </a>
@endsection

@section('content')
<div class="py-4 space-y-6">

    {{-- ── KPI Cards ──────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Customers</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalCustomers }}</p>
            <p class="text-xs text-green-600 mt-1">+{{ $newThisMonth }} new this month</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total Revenue</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">₹{{ number_format($totalRevenue, 0) }}</p>
            <p class="text-xs text-green-600 mt-1">₹{{ number_format($thisMonthRevenue, 0) }} this month</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Linked Orders</p>
            <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalOrders }}</p>
            <p class="text-xs text-indigo-600 mt-1">Avg ₹{{ number_format($avgOrderValue, 0) }} per order</p>
        </div>
    </div>

    {{-- ── Charts Row 1 ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Monthly Revenue Trend --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">📈 Monthly Revenue (Last 12 Months)</h2>
            <div style="position:relative;height:240px">
                <canvas id="monthlyRevenueChart"
                        role="img"
                        aria-label="Line chart showing monthly revenue over the last 12 months">
                </canvas>
            </div>
        </div>

        {{-- Top Customers by Revenue --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">🏆 Top Customers by Revenue</h2>
            <div style="position:relative;height:240px">
                <canvas id="topCustomersChart"
                        role="img"
                        aria-label="Horizontal bar chart showing top customers by total spend">
                </canvas>
            </div>
        </div>
    </div>

    {{-- ── Charts Row 2 ─────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- New Customers Per Month --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">👤 New Customers (Last 6 Months)</h2>
            <div style="position:relative;height:200px">
                <canvas id="newCustomersChart"
                        role="img"
                        aria-label="Bar chart showing new customers added per month">
                </canvas>
            </div>
        </div>

        {{-- Walk-in vs Registered + Monthly Orders --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <h2 class="font-semibold text-gray-800 mb-4">📊 Sale Type Breakdown</h2>
            <div style="position:relative;height:200px">
                <canvas id="saleTypeChart"
                        role="img"
                        aria-label="Doughnut chart showing registered customer sales vs walk-in sales">
                </canvas>
            </div>
            <div style="display:flex;justify-content:center;gap:20px;margin-top:10px;font-size:12px;color:var(--color-text-secondary)">
                <span style="display:flex;align-items:center;gap:5px">
                    <span style="width:10px;height:10px;border-radius:2px;background:#6366f1;display:inline-block"></span>
                    Registered ({{ $registeredSales }})
                </span>
                <span style="display:flex;align-items:center;gap:5px">
                    <span style="width:10px;height:10px;border-radius:2px;background:#d1d5db;display:inline-block"></span>
                    Walk-in ({{ $walkInSales }})
                </span>
            </div>
        </div>
    </div>

    {{-- ── Top Customers Table + Recent Sales ─────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Top Customers Table --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">💰 Top Customers</h2>
                <a href="{{ route('customers.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-100">
                    <tr class="text-left text-gray-500 font-medium">
                        <th class="px-4 py-2">#</th>
                        <th class="px-4 py-2">Customer</th>
                        <th class="px-4 py-2">Orders</th>
                        <th class="px-4 py-2">Total Spent</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($topCustomers as $i => $customer)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3">
                            <span class="w-6 h-6 rounded-full text-xs font-bold flex items-center justify-center
                                {{ $i === 0 ? 'bg-yellow-100 text-yellow-700' : ($i === 1 ? 'bg-gray-100 text-gray-600' : ($i === 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-500')) }}">
                                {{ $i + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('customers.show', $customer) }}"
                               class="font-medium text-indigo-600 hover:underline">{{ $customer->name }}</a>
                            @if($customer->phone)
                                <p class="text-xs text-gray-400">{{ $customer->phone }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $customer->sales_count }}</td>
                        <td class="px-4 py-3 font-semibold text-green-700">
                            ₹{{ number_format($customer->sales_sum_total_amount ?? 0, 0) }}
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-4 py-6 text-center text-gray-400">No customers yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Sales --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-800">🧾 Recent Sales</h2>
                <a href="{{ route('sales.index') }}" class="text-xs text-indigo-600 hover:underline">View all</a>
            </div>
            <div class="divide-y divide-gray-50">
                @forelse($recentSales as $sale)
                <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition">
                    <div>
                        <p class="text-sm font-medium text-gray-800">
                            {{ $sale->customer?->name ?? $sale->customer_name ?? 'Walk-in' }}
                        </p>
                        <p class="text-xs text-gray-400">
                            {{ $sale->reference }} · {{ $sale->sale_date->format('d M Y') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-semibold text-green-700">₹{{ number_format($sale->total_amount, 0) }}</p>
                        <a href="{{ route('sales.show', $sale) }}" class="text-xs text-indigo-500 hover:underline">View</a>
                    </div>
                </div>
                @empty
                <p class="px-5 py-4 text-sm text-gray-400">No sales yet.</p>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
const gridColor = 'rgba(107,114,128,0.1)';

// ── Monthly Revenue Chart ─────────────────────────────────────
new Chart(document.getElementById('monthlyRevenueChart'), {
    type: 'line',
    data: {
        labels: @json($monthlyLabels),
        datasets: [{
            label: 'Revenue (₹)',
            data: @json($monthlyRevenue),
            borderColor: '#22c55e',
            backgroundColor: 'rgba(34,197,94,0.08)',
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 6,
            tension: 0.4,
            fill: true,
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

// ── Top Customers Chart ───────────────────────────────────────
new Chart(document.getElementById('topCustomersChart'), {
    type: 'bar',
    data: {
        labels: @json($topCustomerLabels),
        datasets: [{
            label: 'Total Spent (₹)',
            data: @json($topCustomerData),
            backgroundColor: [
                'rgba(34,197,94,0.8)',
                'rgba(99,102,241,0.8)',
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

// ── New Customers Chart ───────────────────────────────────────
new Chart(document.getElementById('newCustomersChart'), {
    type: 'bar',
    data: {
        labels: @json($newCustLabels),
        datasets: [{
            label: 'New Customers',
            data: @json($newCustData),
            backgroundColor: 'rgba(14,165,233,0.75)',
            borderRadius: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: gridColor }, ticks: { stepSize: 1 } }
        }
    }
});

// ── Sale Type Doughnut ────────────────────────────────────────
new Chart(document.getElementById('saleTypeChart'), {
    type: 'doughnut',
    data: {
        labels: ['Registered Customers', 'Walk-in'],
        datasets: [{
            data: [{{ $registeredSales }}, {{ $walkInSales }}],
            backgroundColor: ['rgba(99,102,241,0.85)', 'rgba(209,213,219,0.85)'],
            borderWidth: 0,
            hoverOffset: 4,
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '65%',
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: ctx => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        const pct = total ? Math.round((ctx.raw / total) * 100) : 0;
                        return ` ${ctx.raw} sales (${pct}%)`;
                    }
                }
            }
        }
    }
});
</script>
@endpush
