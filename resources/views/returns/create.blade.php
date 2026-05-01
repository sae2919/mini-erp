@extends('layouts.app')
@section('title', 'Process Return')
@section('heading', 'Process Return — ' . $sale->reference)

@section('header-actions')
    <a href="{{ route('sales.show', $sale) }}" class="text-sm text-gray-500 hover:text-gray-700">← Back to Sale</a>
@endsection

@section('content')
<div class="py-4 max-w-3xl">
<form method="POST" action="{{ route('returns.store', $sale) }}">
@csrf

<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
            <select name="reason" required
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                <option value="">-- Select Reason --</option>
                @foreach(\App\Models\SaleReturn::reasons() as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes" placeholder="Additional details"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
    </div>

    <div class="p-6">
        <h3 class="font-medium text-gray-800 mb-4">Select Items to Return</h3>
        <table class="w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500 border-b border-gray-100">
                    <th class="pb-2 font-medium">Product</th>
                    <th class="pb-2 font-medium">Originally Sold</th>
                    <th class="pb-2 font-medium">Price</th>
                    <th class="pb-2 font-medium w-28">Return Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($sale->items as $item)
                <tr>
                    <td class="py-3 pr-4 font-medium text-gray-800">{{ $item->product->name }}</td>
                    <td class="py-3 pr-4 text-gray-600">{{ $item->quantity }} {{ $item->product->unit }}</td>
                    <td class="py-3 pr-4 text-gray-600">₹{{ number_format($item->selling_price, 2) }}</td>
                    <td class="py-3">
                        <input type="hidden" name="items[{{ $loop->index }}][sale_item_id]" value="{{ $item->id }}">
                        <input type="number"
                               name="items[{{ $loop->index }}][quantity]"
                               value="0" min="0" max="{{ $item->quantity }}"
                               class="w-24 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 bg-yellow-50 border-yellow-100">
        <p class="text-sm text-yellow-800">
            ⚠️ Stock will be <strong>restored</strong> for returned quantities. This action cannot be undone.
        </p>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('sales.show', $sale) }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-orange-600 text-white rounded-lg font-medium hover:bg-orange-700 transition">
            ↩️ Process Return
        </button>
    </div>
</div>
</form>
</div>
@endsection
