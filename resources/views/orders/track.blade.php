@extends('layouts.storefront')
@section('title', 'Order ' . $sale->reference)

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 rounded-xl px-4 py-3 text-sm mb-6 text-center">
        🎉 {{ session('success') }}
    </div>
    @endif

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="bg-indigo-600 text-white px-6 py-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-200 text-xs font-medium uppercase tracking-wide">Order Reference</p>
                    <p class="text-2xl font-bold mt-1">{{ $sale->reference }}</p>
                </div>
                <span class="px-3 py-1.5 text-sm font-semibold rounded-full bg-white/20 text-white">
                    {{ $sale->statusLabel() }}
                </span>
            </div>
        </div>

        {{-- Status Timeline --}}
        @php
            $steps = ['pending','confirmed','processing','shipped','delivered'];
            $currentIndex = array_search($sale->status, $steps);
        @endphp
        @if($sale->status !== 'cancelled' && in_array($sale->status, $steps))
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="flex items-center justify-between">
                @foreach($steps as $i => $step)
                <div class="flex flex-col items-center flex-1">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                        {{ $i <= $currentIndex ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                        {{ $i < $currentIndex ? '✓' : $i + 1 }}
                    </div>
                    <p class="text-xs mt-1 {{ $i <= $currentIndex ? 'text-indigo-600 font-medium' : 'text-gray-400' }} capitalize">
                        {{ $step }}
                    </p>
                </div>
                @if($i < count($steps) - 1)
                <div class="flex-1 h-0.5 mb-5 {{ $i < $currentIndex ? 'bg-indigo-600' : 'bg-gray-200' }}"></div>
                @endif
                @endforeach
            </div>
        </div>
        @endif

        {{-- Details --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 text-xs mb-1">Order Date</p>
                    <p class="font-medium">{{ $sale->sale_date->format('d M Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs mb-1">Deliver To</p>
                    <p class="font-medium">{{ $sale->shipping_name ?? $sale->customer_name ?? 'N/A' }}</p>
                </div>
                @if($sale->shipping_address)
                <div class="col-span-2">
                    <p class="text-gray-500 text-xs mb-1">Address</p>
                    <p class="font-medium">{{ $sale->shipping_address }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="px-6 py-5 border-b border-gray-100">
            <h3 class="font-semibold text-gray-800 mb-3 text-sm">Items Ordered</h3>
            <div class="space-y-2">
                @foreach($sale->items as $item)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-700">{{ $item->product->name }} × {{ $item->quantity }}</span>
                    <span class="font-medium text-gray-900">₹{{ number_format($item->subtotal, 2) }}</span>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Total --}}
        <div class="px-6 py-4 flex justify-between font-bold text-lg bg-gray-50">
            <span>Total</span>
            <span class="text-indigo-700">₹{{ number_format($sale->total_amount, 2) }}</span>
        </div>
    </div>

    <div class="mt-4 text-center">
        <a href="{{ route('shop.index') }}" class="text-indigo-600 text-sm hover:underline">← Continue Shopping</a>
    </div>
</div>
@endsection
