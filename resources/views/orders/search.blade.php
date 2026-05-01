@extends('layouts.storefront')
@section('title', 'Track Order')

@section('content')
<div class="max-w-md mx-auto px-4 py-16">
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
        <p class="text-4xl mb-4">📦</p>
        <h1 class="text-xl font-bold text-gray-900 mb-2">Track Your Order</h1>
        <p class="text-gray-500 text-sm mb-6">Enter your order reference number</p>

        <form method="POST" action="{{ route('orders.search.post') }}">
            @csrf
            <input type="text" name="reference" placeholder="e.g. ORD-000001"
                   class="w-full border border-gray-300 rounded-xl px-4 py-3 text-sm text-center uppercase focus:ring-2 focus:ring-indigo-400 focus:border-transparent mb-4"
                   autofocus>
            @error('reference')<p class="text-red-500 text-xs mb-3">{{ $message }}</p>@enderror
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-3 rounded-xl font-semibold hover:bg-indigo-700 transition">
                Track Order →
            </button>
        </form>
    </div>
</div>
@endsection
