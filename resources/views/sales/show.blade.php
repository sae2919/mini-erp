@extends('layouts.app')
@section('title', $sale->reference)
@section('heading', 'Invoice — ' . $sale->reference)

@section('header-actions')
    <a href="{{ route('invoices.download', $sale) }}"
       class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition">
        📄 Download PDF
    </a>
    <a href="{{ route('invoices.preview', $sale) }}" target="_blank"
       class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition">
        👁 Preview
    </a>
    @role('admin|sales_executive')
    @if(!in_array($sale->status, ['cancelled']))
    <a href="{{ route('returns.create', $sale) }}"
       class="bg-orange-100 text-orange-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-orange-200 transition">
        ↩️ Return
    </a>
    @endif
    @endrole
    <a href="{{ route('sales.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    {{-- ── Sale Details ──────────────────────────────────────── --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 md:col-span-2">
            <h2 class="font-semibold text-gray-800 mb-4">Sale Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">Reference</dt>
                <dd class="font-mono font-medium">{{ $sale->reference }}</dd>
                <dt class="text-gray-500">Customer</dt>
                <dd>
                    @if($sale->customer)
                        <a href="{{ route('customers.show', $sale->customer) }}" class="text-indigo-600 hover:underline">
                            {{ $sale->customer->name }}
                        </a>
                    @else
                        {{ $sale->customer_name ?: 'Walk-in' }}
                    @endif
                </dd>
                <dt class="text-gray-500">Date</dt>
                <dd>{{ $sale->sale_date->format('d M Y') }}</dd>
                <dt class="text-gray-500">Order Type</dt>
                <dd>
                    <span class="px-2 py-0.5 text-xs rounded-full bg-gray-100 text-gray-600 capitalize">
                        {{ $sale->order_type ?? 'offline' }}
                    </span>
                </dd>
                @if(isset($sale->status))
                <dt class="text-gray-500">Status</dt>
                <dd>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $sale->statusColor() }}">
                        {{ $sale->statusLabel() }}
                    </span>
                </dd>
                @endif
                <dt class="text-gray-500">Notes</dt>
                <dd>{{ $sale->notes ?: '—' }}</dd>
            </dl>
        </div>

        <div class="space-y-3">
            <div class="bg-green-50 border border-green-100 rounded-xl p-5 text-center">
                <p class="text-sm text-green-600 font-medium">Total</p>
                <p class="text-3xl font-bold text-green-800 mt-1">₹{{ number_format($sale->total_amount, 2) }}</p>
                <p class="text-sm text-indigo-600 font-medium mt-1">
                    Profit: ₹{{ number_format($sale->totalProfit(), 2) }}
                </p>
            </div>

            {{-- Payment Status --}}
            @if(isset($sale->payment_status))
            @php $paid = $sale->payments->sum('amount') ?? 0; $balance = $sale->total_amount - $paid; @endphp
            <div class="bg-white border border-gray-100 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-500 font-medium mb-1">Payment Status</p>
                <span class="px-3 py-1 text-sm font-semibold rounded-full
                    {{ $sale->payment_status === 'paid' ? 'bg-green-100 text-green-700' : ($sale->payment_status === 'partial' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">
                    {{ ucfirst($sale->payment_status ?? 'unpaid') }}
                </span>
                @if($balance > 0)
                <p class="text-sm text-red-600 mt-2 font-medium">Balance: ₹{{ number_format($balance, 2) }}</p>
                @endif
            </div>
            @endif
        </div>
    </div>

    {{-- ── Line Items ────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Line Items</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Product</th>
                    <th class="px-4 py-2">SKU</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2">Price</th>
                    <th class="px-4 py-2">Cost</th>
                    <th class="px-4 py-2">Subtotal</th>
                    <th class="px-4 py-2">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($sale->items as $item)
                <tr>
                    <td class="px-4 py-3 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="px-4 py-3 font-mono text-xs text-gray-500">{{ $item->product->sku }}</td>
                    <td class="px-4 py-3">{{ $item->quantity }}</td>
                    <td class="px-4 py-3 text-green-700">₹{{ number_format($item->selling_price, 2) }}</td>
                    <td class="px-4 py-3 text-gray-500">₹{{ number_format($item->cost_price, 2) }}</td>
                    <td class="px-4 py-3 font-semibold">₹{{ number_format($item->subtotal, 2) }}</td>
                    <td class="px-4 py-3 font-semibold text-indigo-700">₹{{ number_format($item->profit(), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="border-t-2 border-gray-200">
                <tr>
                    <td colspan="5" class="px-4 py-3 text-right font-bold text-gray-700">Total</td>
                    <td class="px-4 py-3 font-bold text-green-800">₹{{ number_format($sale->total_amount, 2) }}</td>
                    <td class="px-4 py-3 font-bold text-indigo-800">₹{{ number_format($sale->totalProfit(), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- ── Payment History + Record Payment ─────────────────── --}}
    @role('admin|sales_executive')
    @php $payments = $sale->payments ?? collect(); $paid = $payments->sum('amount'); $balance = $sale->total_amount - $paid; @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Payment History --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">💳 Payment History</div>
            @forelse($payments as $payment)
            <div class="flex items-center justify-between px-5 py-3 border-b border-gray-50">
                <div>
                    <p class="text-sm font-medium text-gray-800">
                        {{ \App\Models\Payment::methods()[$payment->method] ?? $payment->method }}
                    </p>
                    <p class="text-xs text-gray-400">{{ $payment->paid_at->format('d M Y') }} {{ $payment->reference ? '· '.$payment->reference : '' }}</p>
                </div>
                <div class="flex items-center gap-3">
                    <span class="font-semibold text-green-700">₹{{ number_format($payment->amount, 2) }}</span>
                    <form method="POST" action="{{ route('payments.destroy', $payment) }}"
                          onsubmit="return confirm('Delete this payment?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-400 hover:text-red-600">✕</button>
                    </form>
                </div>
            </div>
            @empty
            <p class="px-5 py-4 text-sm text-gray-400">No payments recorded yet.</p>
            @endforelse

            @if($payments->count())
            <div class="px-5 py-3 bg-gray-50 flex justify-between text-sm font-semibold">
                <span class="text-gray-600">Total Paid</span>
                <span class="text-green-700">₹{{ number_format($paid, 2) }}</span>
            </div>
            @if($balance > 0)
            <div class="px-5 py-2 flex justify-between text-sm font-bold border-t border-gray-100">
                <span class="text-gray-600">Balance Due</span>
                <span class="text-red-600">₹{{ number_format($balance, 2) }}</span>
            </div>
            @endif
            @endif
        </div>

        {{-- Record Payment --}}
        @if($balance > 0.01)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">Record Payment</div>
            <form method="POST" action="{{ route('payments.store', $sale) }}" class="p-5 space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Amount (₹) *</label>
                        <input type="number" name="amount" step="0.01" min="0.01"
                               max="{{ $balance }}" value="{{ number_format($balance, 2, '.', '') }}"
                               required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 mb-1">Date *</label>
                        <input type="date" name="paid_at" value="{{ date('Y-m-d') }}"
                               required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                    </div>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Method *</label>
                    <select name="method" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
                        @foreach(\App\Models\Payment::methods() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Reference / TXN ID</label>
                    <input type="text" name="reference" placeholder="UPI ID, cheque no., etc."
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400">
                </div>
                <button type="submit"
                        class="w-full py-2.5 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700 transition">
                    💳 Record Payment
                </button>
            </form>
        </div>
        @else
        <div class="bg-green-50 border border-green-100 rounded-xl p-6 flex items-center justify-center">
            <div class="text-center">
                <p class="text-3xl mb-2">✅</p>
                <p class="font-semibold text-green-700">Fully Paid</p>
                <p class="text-sm text-green-600 mt-1">₹{{ number_format($sale->total_amount, 2) }}</p>
            </div>
        </div>
        @endif
    </div>
    @endrole

    {{-- Cancel button --}}
    @role('admin')
    @if(!in_array($sale->status ?? '', ['cancelled']))
    <div class="flex justify-end">
        <form method="POST" action="{{ route('sales.destroy', $sale) }}"
              onsubmit="return confirm('Cancel this sale? Stock will be restored.')">
            @csrf @method('DELETE')
            <button class="px-4 py-2 text-sm bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition">
                ❌ Cancel Sale
            </button>
        </form>
    </div>
    @endif
    @endrole

</div>
@endsection
