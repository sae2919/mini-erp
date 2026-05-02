@extends('layouts.app')
@section('title','Seller Performance')
@section('heading','Seller Performance Comparison')

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Generate</button>
    </form>

    {{-- Chart --}}
    @if($sellers->count())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h2 class="font-semibold text-gray-800 mb-4">Sales Comparison</h2>
        <div style="position:relative;height:250px">
            <canvas id="perfChart"></canvas>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Rank</th>
                    <th class="px-4 py-3">Seller</th>
                    <th class="px-4 py-3">Region</th>
                    <th class="px-4 py-3">Sales Count</th>
                    <th class="px-4 py-3">Sales Amount</th>
                    <th class="px-4 py-3">Commission</th>
                    <th class="px-4 py-3">Dispatched</th>
                    <th class="px-4 py-3">Stock (units)</th>
                    <th class="px-4 py-3">Outstanding</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($sellers as $i => $row)
                <tr class="hover:bg-gray-50 {{ $i === 0 ? 'bg-yellow-50/50' : '' }}">
                    <td class="px-4 py-3 font-bold text-lg">
                        {{ $i === 0 ? '🥇' : ($i === 1 ? '🥈' : ($i === 2 ? '🥉' : $i+1)) }}
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ route('sellers.show', $row['seller']) }}" class="font-medium text-indigo-600 hover:underline">
                            {{ $row['seller']->name }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-500">{{ $row['seller']->region ?: '—' }}</td>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $row['sales_count'] }}</td>
                    <td class="px-4 py-3 font-semibold text-green-700">₹{{ number_format($row['sales_amount'], 0) }}</td>
                    <td class="px-4 py-3 text-purple-600">₹{{ number_format($row['commission'], 0) }}</td>
                    <td class="px-4 py-3 text-blue-700">₹{{ number_format($row['dispatched'], 0) }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ number_format($row['stock_value']) }}</td>
                    <td class="px-4 py-3 font-medium {{ $row['outstanding'] > 0 ? 'text-red-600' : 'text-gray-400' }}">
                        ₹{{ number_format($row['outstanding'], 0) }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="px-4 py-8 text-center text-gray-400">No data found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
@if($sellers->count())
new Chart(document.getElementById('perfChart'), {
    type: 'bar',
    data: {
        labels: @json($sellers->pluck('seller')->pluck('name')),
        datasets: [
            { label: 'Sales Amount', data: @json($sellers->pluck('sales_amount')), backgroundColor: 'rgba(34,197,94,0.75)', borderRadius: 4 },
            { label: 'Dispatched',   data: @json($sellers->pluck('dispatched')),   backgroundColor: 'rgba(99,102,241,0.65)', borderRadius: 4 },
            { label: 'Commission',   data: @json($sellers->pluck('commission')),   backgroundColor: 'rgba(168,85,247,0.65)', borderRadius: 4 },
        ]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { boxWidth: 10 } } },
        scales: {
            x: { grid: { display: false } },
            y: { ticks: { callback: v => '₹'+(v>=1000?(v/1000).toFixed(0)+'k':v) } }
        }
    }
});
@endif
</script>
@endpush
