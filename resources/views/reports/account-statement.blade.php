@extends('layouts.app')
@section('title','Account Statement')
@section('heading','Seller Account Statement')

@section('header-actions')
    @if(isset($seller))
    <a href="javascript:window.print()"
       class="bg-gray-700 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800 transition">
        🖨️ Print
    </a>
    @endif
@endsection

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">Seller *</label>
            <select name="seller_id" required class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">-- Select Seller --</option>
                @foreach($sellers as $s)
                <option value="{{ $s->id }}" {{ isset($seller) && $seller->id==$s->id?'selected':'' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ $from ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ $to ?? '' }}" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700">Generate</button>
    </form>

    @if(isset($seller))

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-xs text-blue-600 font-medium">Total Dispatched</p>
            <p class="text-xl font-bold text-blue-800 mt-1">₹{{ number_format($totalDispatched, 2) }}</p>
        </div>
        <div class="bg-green-50 border border-green-100 rounded-xl p-4">
            <p class="text-xs text-green-600 font-medium">Total Collected</p>
            <p class="text-xl font-bold text-green-800 mt-1">₹{{ number_format($totalCollected, 2) }}</p>
        </div>
        <div class="bg-orange-50 border border-orange-100 rounded-xl p-4">
            <p class="text-xs text-orange-600 font-medium">Balance Due</p>
            <p class="text-xl font-bold text-orange-800 mt-1">₹{{ number_format(max($balance, 0), 2) }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-100 rounded-xl p-4">
            <p class="text-xs text-purple-600 font-medium">Commission Earned</p>
            <p class="text-xl font-bold text-purple-800 mt-1">₹{{ number_format($totalCommissions, 2) }}</p>
        </div>
    </div>

    {{-- Seller Info --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <div class="flex items-center justify-between mb-2">
            <h2 class="font-semibold text-gray-800 text-lg">{{ $seller->name }}</h2>
            <span class="text-sm text-gray-500">{{ $from }} to {{ $to }}</span>
        </div>
        <p class="text-sm text-gray-500">{{ $seller->region }} | {{ $seller->phone }} | {{ $seller->email }}</p>
    </div>

    {{-- Transaction Ledger --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">📋 Transaction Ledger</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Type</th>
                    <th class="px-4 py-2 text-right">Debit (₹)</th>
                    <th class="px-4 py-2 text-right">Credit (₹)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @php $runningBalance = 0; @endphp
                @foreach($transactions as $t)
                @php $runningBalance += $t['debit'] - $t['credit']; @endphp
                <tr class="hover:bg-gray-50 {{ $t['type']==='payment' ? 'bg-green-50/30' : ($t['type']==='dispatch' ? 'bg-blue-50/30' : '') }}">
                    <td class="px-4 py-2.5 text-gray-600">{{ $t['date']->format('d M Y') }}</td>
                    <td class="px-4 py-2.5 text-gray-800">{{ $t['description'] }}</td>
                    <td class="px-4 py-2.5">
                        <span class="px-2 py-0.5 text-xs rounded-full
                            {{ $t['type']==='dispatch' ? 'bg-blue-100 text-blue-700' : 'bg-green-100 text-green-700' }}">
                            {{ ucfirst($t['type']) }}
                        </span>
                    </td>
                    <td class="px-4 py-2.5 text-right font-medium {{ $t['debit']>0?'text-red-600':'text-gray-300' }}">
                        {{ $t['debit']>0 ? '₹'.number_format($t['debit'],2) : '—' }}
                    </td>
                    <td class="px-4 py-2.5 text-right font-medium {{ $t['credit']>0?'text-green-600':'text-gray-300' }}">
                        {{ $t['credit']>0 ? '₹'.number_format($t['credit'],2) : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                <tr class="font-bold">
                    <td colspan="3" class="px-4 py-3 text-right text-gray-700">Balance Due</td>
                    <td colspan="2" class="px-4 py-3 text-right text-lg {{ $balance>0?'text-red-600':'text-green-600' }}">
                        ₹{{ number_format(abs($balance), 2) }}
                        {{ $balance > 0 ? '(Receivable)' : '(Clear)' }}
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Sales by this seller --}}
    @if($sales->count())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">💰 Sales to Customers</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Reference</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2 text-right">Sale Amount</th>
                    <th class="px-4 py-2 text-right">Commission</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($sales as $s)
                <tr>
                    <td class="px-4 py-2 text-gray-600">{{ $s['date']->format('d M Y') }}</td>
                    <td class="px-4 py-2 font-mono text-green-600">{{ $s['ref'] }}</td>
                    <td class="px-4 py-2 text-gray-700">{{ $s['description'] }}</td>
                    <td class="px-4 py-2 text-right font-medium text-gray-800">₹{{ number_format($s['sale_amount'], 2) }}</td>
                    <td class="px-4 py-2 text-right text-purple-600 font-medium">₹{{ number_format($s['commission'], 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200 bg-gray-50">
                <tr class="font-bold">
                    <td colspan="3" class="px-4 py-3 text-right text-gray-700">Total</td>
                    <td class="px-4 py-3 text-right text-gray-800">₹{{ number_format($totalSales, 2) }}</td>
                    <td class="px-4 py-3 text-right text-purple-700">₹{{ number_format($totalCommissions, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
    @endif

    @endif
</div>
@endsection
