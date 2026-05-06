<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'ERP') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="h-full font-sans">
<div class="flex h-full">

    <aside class="w-64 bg-gray-900 text-white flex flex-col flex-shrink-0">
        <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
            <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            <span class="text-lg font-bold tracking-wide">{{ config('app.name') }}</span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm">
            @php
                function navLink(string $route, string $icon, string $label): string {
                    $active = request()->routeIs($route) || request()->routeIs($route . '*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-300 hover:bg-gray-700 hover:text-white';
                    return '<a href="' . route($route) . '" class="flex items-center gap-3 px-3 py-2 rounded-lg transition ' . $active . '">
                        <span style="font-size:15px">' . $icon . '</span><span>' . $label . '</span></a>';
                }
            @endphp

            {{-- ════════ SELLER MENU ════════ --}}
            @role('seller')
            <div class="pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">My Store</div>
            {!! navLink('dashboard',              '📊', 'My Dashboard') !!}
            {!! navLink('seller-pos.index',       '🖥️',  'POS Terminal') !!}
            {!! navLink('seller-sales.index',     '💰', 'My Sales') !!}
            {!! navLink('seller-sales.create',    '➕', 'Record Sale') !!}
            {!! navLink('seller-dispatches.index','📦', 'My Dispatches') !!}
            {!! navLink('stock-requests.create',  '📥', 'Request Stock') !!} {{-- ✅ ADDED --}}
            {!! navLink('stock-requests.my',  '📄', 'My Requests') !!}
            @endrole

            {{-- ════════ STAFF MENU ════════ --}}
            @hasanyrole('admin|manager|inventory_manager|sales_executive|viewer')

                <div class="pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dashboard</div>
                {!! navLink('dashboard', '📊', 'Overview') !!}

                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Products</div>
                @role('admin'){!! navLink('categories.index', '🏷️', 'Categories') !!}@endrole
                {!! navLink('products.index', '📦', 'Products') !!}
                

                @hasanyrole('admin|manager|inventory_manager')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Production</div>
                {!! navLink('productions.index', '🏭', 'Production Batches') !!}
                @hasanyrole('admin|manager')
                {!! navLink('productions.create', '➕', 'Record Production') !!}
                {!! navLink('stock-requests.admin', '📦', 'Stock Requests') !!}
                @endhasanyrole
                @endhasanyrole

                @hasanyrole('admin|sales_executive')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sellers</div>
                {!! navLink('sellers.index',     '🏪', 'Sellers') !!}
                {!! navLink('dispatches.index',  '📤', 'Dispatch Orders') !!}
                {!! navLink('dispatches.create', '➕', 'New Dispatch') !!}
                {!! navLink('commissions.index', '💰', 'Commissions') !!}
                @endhasanyrole

                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sales</div>
                {!! navLink('seller-sales.index', '💰', 'Seller Sales') !!}
                @hasanyrole('admin|sales_executive')
                {!! navLink('seller-pos.index',   '🖥️',  'POS Terminal') !!}
                @endhasanyrole

                @hasanyrole('admin|viewer')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reports</div>
                {!! navLink('reports.seller-pnl',         '📊', 'Seller P&L') !!}
                
                {!! navLink('reports.best-products',      '📈', 'Best Products') !!}
                {!! navLink('reports.seller-performance', '🏪', 'Seller Performance') !!}
                
                
                @endhasanyrole

                @role('admin')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</div>
                {!! navLink('users.index',         '👥', 'Users') !!}
                {!! navLink('activity-logs.index', '🔍', 'Activity Log') !!}
                @endrole

            @endhasanyrole
        </nav>
@if(session('success'))
    <div style="background:#d1fae5;color:#065f46;padding:10px;margin:10px;border-radius:6px;">
        {{ session('success') }}
    </div>
@endif
        <div class="border-t border-gray-700 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm text-gray-200 font-medium truncate">{{ Auth::user()->name }}</p>
                    @php
                        $role = Auth::user()->roles->first()?->name ?? 'user';
                        $badges = [
                            'admin'             => ['🥇 Admin',           'text-yellow-400'],
                            'manager'           => ['🥈 Manager',         'text-blue-400'],
                            'inventory_manager' => ['🥈 Inv. Manager',    'text-blue-400'],
                            'sales_executive'   => ['🥉 Sales Exec',      'text-green-400'],
                            'seller'            => ['🏪 Seller',          'text-pink-400'],
                            'viewer'            => ['👁 Viewer',          'text-gray-400'],
                        ];
                        [$badge, $color] = $badges[$role] ?? [$role, 'text-gray-400'];
                    @endphp
                    <p class="text-xs {{ $color }}">{{ $badge }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs text-gray-400 hover:text-white ml-2 transition">Logout</button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">@yield('heading','Dashboard')</h1>
            <div class="flex items-center gap-4">
                @yield('header-actions')
                @php
                    $nc = 0;
                    try { $nc = \App\Models\ErpNotification::forUser(auth()->id())->unread()->count(); } catch(\Exception $e) {}
                @endphp
                <a href="{{ route('notifications.index') }}" class="relative text-gray-500 hover:text-indigo-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($nc > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">
                        {{ $nc > 9 ? '9+' : $nc }}
                    </span>
                    @endif
                </a>
            </div>
        </header>

        <div class="px-6 pt-4">
            @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm mb-4">✅ {{ session('success') }}</div>
            @endif
            @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-4">❌ {{ session('error') }}</div>
            @endif
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-4">
                <p class="font-medium mb-1">Please fix the following:</p>
                <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
            @endif
        </div>

        <main class="flex-1 overflow-y-auto px-6 pb-6">@yield('content')</main>
    </div>
</div>
@stack('scripts')
</body>
</html>