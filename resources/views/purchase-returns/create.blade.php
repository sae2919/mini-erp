@extends('layouts.app')
@section('title', 'Process Purchase Return')
@section('heading', 'Purchase Return — ' . $purchase->reference)

@section('header-actions')
    <a href="{{ route('purchases.show', $purchase) }}"
       class="text-sm text-gray-500 hover:text-gray-700">← Back to Purchase</a>
@endsection

@section('content')
<div class="py-4 max-w-3xl">

    {{-- Purchase Info --}}
    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-5 text-sm">
        <div class="grid grid-cols-3 gap-4">
            <div>
                <p class="text-xs text-blue-600 font-medium">Purchase Reference</p>
                <p class="font-bold text-blue-900 mt-0.5">{{ $purchase->reference }}</p>
            </div>
            <div>
                <p class="text-xs text-blue-600 font-medium">Supplier</p>
                <p class="font-bold text-blue-900 mt-0.5">{{ $purchase->supplier?->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-blue-600 font-medium">Purchase Date</p>
                <p class="font-bold text-blue-900 mt-0.5">{{ $purchase->purchase_date->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('purchase-returns.store', $purchase) }}">
    @csrf

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

        {{-- Reason + Notes --}}
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                <select name="reason" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                    <option value="">-- Select Reason --</option>
                    @foreach(\App\Models\PurchaseReturn::reasons() as $value => $label)
                        <option value="{{ $value }}" {{ old('reason') === $value ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @error('reason')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <input type="text" name="notes" value="{{ old('notes') }}"
                       placeholder="Additional details..."
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            </div>
        </div>

        {{-- Items --}}
        <div class="p-6">
            <h3 class="font-semibold text-gray-800 mb-4">Select Items to Return to Supplier</h3>

            @error('items')<p class="text-red-500 text-sm mb-3">{{ $message }}</p>@enderror

            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-gray-500 border-b border-gray-100 text-xs font-medium">
                        <th class="pb-2">Product</th>
                        <th class="pb-2">Purchased Qty</th>
                        <th class="pb-2">Already Returned</th>
                        <th class="pb-2">Cost Price</th>
                        <th class="pb-2 w-28">Return Qty</th>
                        <th class="pb-2 w-28">Return Price</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($purchase->items as $index => $item)
                    @php
                        $alreadyReturned = $returnedQtys[$item->product_id] ?? 0;
                        $maxReturnable   = max(0, $item->quantity - $alreadyReturned);
                    @endphp
                    <tr>
                        <td class="py-3 pr-4">
                            <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $item->product_id }}">
                            <p class="font-medium text-gray-800">{{ $item->product->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $item->product->sku }}</p>
                        </td>
                        <td class="py-3 pr-4 text-gray-600">
                            {{ $item->quantity }} {{ $item->product->unit }}
                        </td>
                        <td class="py-3 pr-4">
                            @if($alreadyReturned > 0)
                                <span class="text-orange-500 font-medium">{{ $alreadyReturned }}</span>
                            @else
                                <span class="text-gray-400">0</span>
                            @endif
                        </td>
                        <td class="py-3 pr-4 text-gray-600">
                            ₹{{ number_format($item->unit_price, 2) }}
                        </td>
                        <td class="py-3 pr-2">
                            @if($maxReturnable > 0)
                                <input type="number"
                                       name="items[{{ $index }}][quantity]"
                                       value="0" min="0" max="{{ $maxReturnable }}"
                                       class="w-24 border border-gray-300 rounded-lg px-3 py-1.5 text-sm
                                              focus:ring-2 focus:ring-indigo-400 focus:border-transparent
                                              qty-input"
                                       oninput="updateSubtotal(this)">
                                <p class="text-xs text-gray-400 mt-0.5">max {{ $maxReturnable }}</p>
                            @else
                                <span class="text-xs text-gray-400 italic">Fully returned</span>
                                <input type="hidden" name="items[{{ $index }}][quantity]" value="0">
                            @endif
                        </td>
                        <td class="py-3">
                            <input type="number"
                                   name="items[{{ $index }}][price]"
                                   value="{{ $item->unit_price }}"
                                   step="0.01" min="0"
                                   class="w-28 border border-gray-300 rounded-lg px-3 py-1.5 text-sm
                                          focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Total --}}
        <div class="px-6 py-4 bg-orange-50 flex items-center justify-between">
            <div class="text-sm text-orange-700">
                ⚠️ Approved returns will <strong>deduct stock</strong> immediately.
            </div>
            <div class="text-right">
                <p class="text-xs text-gray-500">Return Total</p>
                <p class="text-xl font-bold text-orange-700" id="return-total">₹0.00</p>
            </div>
        </div>

        <div class="px-6 py-4 flex justify-end gap-3">
            <a href="{{ route('purchases.show', $purchase) }}"
               class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                Cancel
            </a>
            <button type="submit"
                    class="px-6 py-2 text-sm bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700 transition">
                ↩️ Process Return
            </button>
        </div>
    </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function updateSubtotal(qtyInput) {
    const row   = qtyInput.closest('tr');
    const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
    const qty   = parseInt(qtyInput.value) || 0;

    let total = 0;
    document.querySelectorAll('.qty-input').forEach(inp => {
        const r = inp.closest('tr');
        const p = parseFloat(r.querySelector('input[name*="[price]"]').value) || 0;
        const q = parseInt(inp.value) || 0;
        total += p * q;
    });
    document.getElementById('return-total').textContent = '₹' + total.toFixed(2);
}
</script>
@endpush
