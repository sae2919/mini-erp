@extends('layouts.app')
@section('title', $product->name)
@section('heading', $product->name)

@section('header-actions')
    <a href="{{ route('products.edit', $product) }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        Edit Product
    </a>
    <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700 ml-2">← Back</a>
@endsection

@section('content')
<div class="py-4 space-y-5">

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        {{-- Product details --}}
        <div class="md:col-span-2 bg-white rounded-xl border border-gray-100 shadow-sm p-6">
            <h2 class="font-semibold text-gray-800 mb-4">Product Details</h2>
            <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                <dt class="text-gray-500">SKU</dt>
                <dd class="font-mono text-gray-800">{{ $product->sku }}</dd>

                <dt class="text-gray-500">Category</dt>
                <dd class="text-gray-800">{{ $product->category->name ?? '—' }}</dd>

                <dt class="text-gray-500">Selling Price</dt>
                <dd class="font-semibold text-green-700">₹{{ number_format($product->price, 2) }}</dd>

                <dt class="text-gray-500">Cost Price</dt>
                <dd class="text-gray-800">₹{{ number_format($product->cost_price, 2) }}</dd>

                <dt class="text-gray-500">Profit Margin</dt>
                <dd class="text-indigo-700 font-medium">{{ $product->profitMargin() }}%</dd>

                <dt class="text-gray-500">Unit</dt>
                <dd class="text-gray-800">{{ $product->unit }}</dd>

                <dt class="text-gray-500">Description</dt>
                <dd class="text-gray-800 col-span-2">{{ $product->description ?: '—' }}</dd>
            </dl>
        </div>

        {{-- Stock card --}}
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col items-center justify-center text-center">
            <p class="text-sm text-gray-500 mb-2">Current Stock</p>
            <p class="text-5xl font-bold {{ $product->stock_quantity == 0 ? 'text-red-600' : ($product->isLowStock() ? 'text-yellow-600' : 'text-green-600') }}">
                {{ $product->stock_quantity }}
            </p>
            <p class="text-sm text-gray-400 mt-1">{{ $product->unit }}</p>
            @if($product->isLowStock())
                <span class="mt-3 px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">
                    ⚠️ Low Stock (threshold: {{ $product->low_stock_threshold }})
                </span>
            @endif
        </div>
    </div>

    {{-- Purchase history --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">📥 Purchase History</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Reference</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Supplier</th>
                    <th class="px-4 py-2">Qty</th>
                    <th class="px-4 py-2">Cost Price</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($product->purchaseItems as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-indigo-600">
                        <a href="{{ route('purchases.show', $item->purchase) }}">{{ $item->purchase->reference }}</a>
                    </td>
                    <td class="px-4 py-2 text-gray-600">{{ $item->purchase->purchase_date->format('d M Y') }}</td>
                    <td class="px-4 py-2 text-gray-600">{{ $item->purchase->supplier->name }}</td>
                    <td class="px-4 py-2 font-medium">{{ $item->quantity }}</td>
                    <td class="px-4 py-2 text-gray-600">₹{{ number_format($item->cost_price, 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-5 text-center text-gray-400">No purchases yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Sales history --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-4 border-b border-gray-100 font-semibold text-gray-800">📤 Sales History</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-2">Reference</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Qty Sold</th>
                    <th class="px-4 py-2">Selling Price</th>
                    <th class="px-4 py-2">Profit</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($product->saleItems as $item)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2 text-indigo-600">
                        <a href="{{ route('sales.show', $item->sale) }}">{{ $item->sale->reference }}</a>
                    </td>
                    <td class="px-4 py-2 text-gray-600">{{ $item->sale->sale_date->format('d M Y') }}</td>
                    <td class="px-4 py-2 font-medium">{{ $item->quantity }}</td>
                    <td class="px-4 py-2 text-gray-600">₹{{ number_format($item->selling_price, 2) }}</td>
                    <td class="px-4 py-2 font-semibold text-green-700">₹{{ number_format($item->profit(), 2) }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-4 py-5 text-center text-gray-400">No sales yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection
