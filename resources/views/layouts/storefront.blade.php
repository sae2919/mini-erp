<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Shop') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 font-sans min-h-screen flex flex-col">

<header class="bg-white border-b border-gray-200 sticky top-0 z-50 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 flex items-center justify-between h-16">
        <a href="{{ route('shop.index') }}" class="flex items-center gap-2">
            <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="text-xl font-bold text-gray-900">{{ config('app.name') }}</span>
        </a>

        <form method="GET" action="{{ route('shop.index') }}" class="flex-1 max-w-md mx-8">
            <div class="relative">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search products..."
                       class="w-full border border-gray-300 rounded-full px-4 py-2 pr-10 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                <button type="submit" class="absolute right-3 top-2.5 text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </button>
            </div>
        </form>

        <div class="flex items-center gap-4">
            <a href="{{ route('orders.search') }}" class="text-sm text-gray-600 hover:text-indigo-600 hidden sm:block">Track Order</a>

            <a href="{{ route('cart.index') }}" class="relative flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-indigo-700 transition">
                🛒 Cart
                @php $count = \App\Http\Controllers\CartController::cartCount(); @endphp
                @if($count > 0)
                <span class="bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">{{ $count }}</span>
                @endif
            </a>

            @auth
                @hasanyrole('admin|inventory_manager|sales_executive|viewer')
                <a href="{{ route('dashboard') }}" class="text-xs text-gray-500 hover:text-indigo-600">⚙️ ERP</a>
                @endhasanyrole
            @endauth
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 pt-4 w-full">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm mb-4">✅ {{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-4">❌ {{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-4">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif
</div>

<main class="flex-1">@yield('content')</main>

<footer class="bg-gray-900 text-gray-400 mt-12">
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col md:flex-row justify-between gap-4 text-sm">
        <p class="font-semibold text-white">{{ config('app.name') }}</p>
        <div class="flex gap-6">
            <a href="{{ route('shop.index') }}" class="hover:text-white">Shop</a>
            <a href="{{ route('cart.index') }}" class="hover:text-white">Cart</a>
            <a href="{{ route('orders.search') }}" class="hover:text-white">Track Order</a>
        </div>
        <p>© {{ date('Y') }} {{ config('app.name') }}</p>
    </div>
</footer>
</body>
</html>
