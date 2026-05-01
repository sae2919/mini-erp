<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mini ERP') — {{ config('app.name') }}</title>
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
            <span class="text-lg font-bold tracking-wide">Mini ERP</span>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto text-sm">
            @php
                function navLink(string $route, string $icon, string $label): string {
                    $active = request()->routeIs($route . '*')
                        ? 'bg-indigo-600 text-white'
                        : 'text-gray-300 hover:bg-gray-700 hover:text-white';
                    return '<a href="' . route($route) . '" class="flex items-center gap-3 px-3 py-2 rounded-lg transition ' . $active . '">
                                <span style="font-size:15px">' . $icon . '</span><span>' . $label . '</span></a>';
                }
            @endphp

            @role('customer')
                <div class="pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Shop</div>
                {!! navLink('products.index', '📦', 'Browse Products') !!}
                {!! navLink('sales.create',   '🛒', 'Place Order') !!}
            @else

                {{-- ── Dashboards ──────────────────────────────── --}}
                <div class="pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dashboards</div>
                {!! navLink('dashboard', '📊', 'Overview') !!}
                @hasanyrole('admin|inventory_manager')
                    {!! navLink('dashboard.suppliers', '🏭', 'Supplier Dashboard') !!}
                @endhasanyrole
                @hasanyrole('admin|sales_executive')
                    {!! navLink('dashboard.customers', '👤', 'Customer Dashboard') !!}
                @endhasanyrole

                {{-- ── Inventory ───────────────────────────────── --}}
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Inventory</div>
                @role('admin'){!! navLink('categories.index', '🏷️', 'Categories') !!}@endrole
                {!! navLink('products.index', '📦', 'Products') !!}
                @hasanyrole('admin|inventory_manager|viewer')
                    {!! navLink('suppliers.index', '🏭', 'Suppliers') !!}
                @endhasanyrole
                @hasanyrole('admin|inventory_manager')
                    {!! navLink('stock-adjustments.index', '⚖️', 'Stock Adjustments') !!}
                @endhasanyrole

                {{-- ── Sales ──────────────────────────────────── --}}
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Sales</div>
                @hasanyrole('admin|sales_executive')
                    {!! navLink('quotations.index',      '📋', 'Quotations') !!}
                    {!! navLink('customers.index',       '👥', 'Customers') !!}
                    {!! navLink('sales.index',           '💰', 'Sales') !!}
                    {!! navLink('returns.index',         '↩️',  'Sale Returns') !!}
                    {!! navLink('payments.index',        '💳', 'Payments') !!}
                    {!! navLink('payments.receivables',  '🧾', 'Receivables') !!}
                @endhasanyrole

                {{-- ── Purchases ──────────────────────────────── --}}
                @hasanyrole('admin|inventory_manager')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Purchases</div>
                {!! navLink('purchases.index',        '🛒', 'Purchases') !!}
                {!! navLink('purchase-returns.index', '↩️',  'Purchase Returns') !!}
                @endhasanyrole

                {{-- ── Finance ────────────────────────────────── --}}
                @role('admin')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Finance</div>
                {!! navLink('expenses.index', '💸', 'Expenses') !!}
                @endrole

                {{-- ── POS ─────────────────────────────────────── --}}
                @hasanyrole('admin|sales_executive')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">POS</div>
                {!! navLink('pos.index', '🖥️', 'POS Terminal') !!}
                @endhasanyrole

                {{-- ── Reports ─────────────────────────────────── --}}
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Reports</div>
                @hasanyrole('admin|viewer')
                    {!! navLink('reports.sales',     '📈', 'Sales Report') !!}
                    {!! navLink('reports.profit',    '💹', 'Profit Report') !!}
                @endhasanyrole
                @hasanyrole('admin|inventory_manager|viewer')
                    {!! navLink('reports.purchases', '📉', 'Purchase Report') !!}
                @endhasanyrole

                {{-- ── Admin ──────────────────────────────────── --}}
                @role('admin')
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Admin</div>
                {!! navLink('users.index',         '👥', 'Users') !!}
                {!! navLink('activity-logs.index', '🔍', 'Activity Log') !!}
                @endrole

                {{-- ── Online Store ───────────────────────────── --}}
                <div class="pt-3 pb-1 px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Online Store</div>
                <a href="{{ route('shop.index') }}" target="_blank"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-700 hover:text-white transition">
                    <span style="font-size:15px">🛍️</span>
                    <span>View Storefront</span>
                    <span class="ml-auto text-xs text-gray-500">↗</span>
                </a>

            @endrole
        </nav>

        {{-- User info + role badge --}}
        <div class="border-t border-gray-700 px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="min-w-0">
                    <p class="text-sm text-gray-200 font-medium truncate">{{ Auth::user()->name }}</p>
                    @php
                        $role = Auth::user()->roles->first()?->name ?? 'user';
                        $roleLabels = [
                            'admin'             => ['🥇 Admin',           'text-yellow-400'],
                            'inventory_manager' => ['🥈 Inventory Mgr',  'text-blue-400'],
                            'sales_executive'   => ['🥉 Sales Exec',      'text-green-400'],
                            'viewer'            => ['👁 Viewer',          'text-gray-400'],
                            'customer'          => ['🛍 Customer',        'text-pink-400'],
                        ];
                        [$roleLabel, $roleColor] = $roleLabels[$role] ?? [$role, 'text-gray-400'];
                    @endphp
                    <p class="text-xs {{ $roleColor }}">{{ $roleLabel }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-xs text-gray-400 hover:text-white transition ml-2">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main --}}
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">

        <header class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-800">@yield('heading', 'Dashboard')</h1>
            <div class="flex items-center gap-4">
                @yield('header-actions')

                {{-- Notification Bell --}}
                @php $notifCount = \App\Models\ErpNotification::forUser(auth()->id())->unread()->count(); @endphp
                <a href="{{ route('notifications.index') }}" class="relative text-gray-500 hover:text-indigo-600 transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($notifCount > 0)
                    <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center font-bold">
                        {{ $notifCount > 9 ? '9+' : $notifCount }}
                    </span>
                    @endif
                </a>
            </div>
        </header>

        <div class="px-6 pt-4">
            @if(session('success'))
                <div class="flex gap-2 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm mb-4">
                    ✅ {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="flex gap-2 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-4">
                    ❌ {{ session('error') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm mb-4">
                    <p class="font-medium mb-1">Please fix the following errors:</p>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
        </div>

        <main class="flex-1 overflow-y-auto px-6 pb-6">@yield('content')</main>
    </div>
</div>
@stack('scripts')
</body>
</html>
