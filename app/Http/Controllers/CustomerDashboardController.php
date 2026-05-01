<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        // ── KPI Cards ─────────────────────────────────────────────
        $totalCustomers     = Customer::count();
        $newThisMonth       = Customer::where('created_at', '>=', Carbon::now()->startOfMonth())->count();
        $totalRevenue       = Sale::sum('total_amount');
        $thisMonthRevenue   = Sale::where('sale_date', '>=', Carbon::now()->startOfMonth())->sum('total_amount');
        $totalOrders        = Sale::whereNotNull('customer_id')->count();
        $avgOrderValue      = $totalOrders ? round(Sale::whereNotNull('customer_id')->sum('total_amount') / $totalOrders, 2) : 0;

        // ── Top 6 customers by spend ──────────────────────────────
        $topCustomers = Customer::withSum('sales', 'total_amount')
            ->withCount('sales')
            ->orderByDesc('sales_sum_total_amount')
            ->limit(6)
            ->get();

        // ── Top customers chart ───────────────────────────────────
        $topCustomerLabels = $topCustomers->pluck('name');
        $topCustomerData   = $topCustomers->pluck('sales_sum_total_amount');

        // ── Monthly revenue last 12 months ────────────────────────
        $last12 = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = Carbon::now()->startOfMonth()->subMonths($monthsAgo);
            return [
                'month'    => $date->format('M Y'),
                'revenue'  => Sale::whereYear('sale_date', $date->year)
                    ->whereMonth('sale_date', $date->month)
                    ->sum('total_amount'),
                'orders'   => Sale::whereYear('sale_date', $date->year)
                    ->whereMonth('sale_date', $date->month)
                    ->count(),
            ];
        });

        $monthlyLabels  = $last12->pluck('month');
        $monthlyRevenue = $last12->pluck('revenue');
        $monthlyOrders  = $last12->pluck('orders');

        // ── New customers per month (last 6 months) ───────────────
        $newCustomers = collect(range(5, 0))->map(function ($monthsAgo) {
            $date = Carbon::now()->startOfMonth()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M Y'),
                'count' => Customer::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->count(),
            ];
        });

        $newCustLabels = $newCustomers->pluck('month');
        $newCustData   = $newCustomers->pluck('count');

        // ── Recent sales ──────────────────────────────────────────
        $recentSales = Sale::with('customer')
            ->orderByDesc('sale_date')
            ->limit(8)
            ->get();

        // ── Walk-in vs registered customer ratio ──────────────────
        $registeredSales = Sale::whereNotNull('customer_id')->count();
        $walkInSales     = Sale::whereNull('customer_id')->count();

        return view('dashboard.customers', compact(
            'totalCustomers', 'newThisMonth', 'totalRevenue',
            'thisMonthRevenue', 'totalOrders', 'avgOrderValue',
            'topCustomers', 'recentSales',
            'monthlyLabels', 'monthlyRevenue', 'monthlyOrders',
            'topCustomerLabels', 'topCustomerData',
            'newCustLabels', 'newCustData',
            'registeredSales', 'walkInSales'
        ));
    }
}
