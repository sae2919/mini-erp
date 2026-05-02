@extends('layouts.app')
@section('title','POS Terminal')
@section('heading','POS Terminal — Select Seller')

@section('content')
<div class="py-4 max-w-2xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
        <p class="text-gray-600 text-sm mb-5">Select which seller's POS terminal you want to open:</p>

        @if($sellers->isEmpty())
            <p class="text-gray-400 text-center py-8">No active sellers found. <a href="{{ route('sellers.create') }}" class="text-indigo-600 hover:underline">Add a seller first →</a></p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            @foreach($sellers as $seller)
            <a href="{{ route('seller-pos.index', ['seller_id' => $seller->id]) }}"
               class="flex items-center gap-4 p-4 border-2 border-gray-100 rounded-xl hover:border-indigo-400 hover:bg-indigo-50 transition group">
                <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center text-indigo-700 font-bold text-lg flex-shrink-0">
                    {{ strtoupper(substr($seller->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-800 group-hover:text-indigo-700">{{ $seller->name }}</p>
                    <p class="text-xs text-gray-400">{{ $seller->region ?: 'No region' }}</p>
                    @php $stockCount = $seller->stocks()->where('quantity','>',0)->count(); @endphp
                    <p class="text-xs text-green-600 mt-0.5">{{ $stockCount }} product(s) in stock</p>
                </div>
                <span class="ml-auto text-indigo-400 group-hover:text-indigo-600 text-xl">→</span>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection