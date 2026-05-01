<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SupplierDashboardController extends Controller
{
    public function index()
    {
        // ── KPI Cards ─────────────────────────────────────────────
        $totalSuppliers      = Supplier::count();
        $activeSuppliers     = Supplier::whereHas('purchases')->count();
        $totalSpent          = Purchase::sum('total_amount');
        $thisMonthSpent      = Purchase::where('purchase_date', '>=', Carbon::now()->startOfMonth())->sum('total_amount');
        $totalOrders         = Purchase::count();
        $avgOrderValue       = $totalOrders ? round($totalSpent / $totalOrders, 2) : 0;

        // ── Top 6 Suppliers by total spend ────────────────────────
        $topSuppliers = Supplier::withSum('purchases', 'total_amount')
            ->withCount('purchases')
            ->orderByDesc('purchases_sum_total_amount')
            ->limit(6)
            ->get();

        // ── Monthly spend per supplier (last 6 months) ────────────
        $months = collect(range(5, 0))->map(fn($m) => Carbon::now()->startOfMonth()->subMonths($m));

        $monthlySpend = DB::table('purchases')
            ->join('suppliers', 'suppliers.id', '=', 'purchases.supplier_id')
            ->whereNull('purchases.deleted_at')
            ->where('purchases.purchase_date', '>=', Carbon::now()->subMonths(6)->startOfMonth())
            ->select([
                'suppliers.name',
                DB::raw("DATE_FORMAT(purchase_date, '%b %Y') as month"),
                DB::raw("DATE_FORMAT(purchase_date, '%Y-%m') as month_key"),
                DB::raw('SUM(total_amount) as total'),
            ])
            ->groupBy('suppliers.id', 'suppliers.name', 'month', 'month_key')
            ->orderBy('month_key')
            ->get();

        // ── Spend last 12 months (bar chart) ─────────────────────
        $last12 = collect(range(11, 0))->map(function ($monthsAgo) {
            $date = Carbon::now()->startOfMonth()->subMonths($monthsAgo);
            return [
                'month' => $date->format('M Y'),
                'total' => Purchase::whereYear('purchase_date', $date->year)
                    ->whereMonth('purchase_date', $date->month)
                    ->sum('total_amount'),
            ];
        });

        $monthlyLabels = $last12->pluck('month');
        $monthlyData   = $last12->pluck('total');

        // ── Top suppliers chart data ───────────────────────────────
        $topSupplierLabels = $topSuppliers->pluck('name');
        $topSupplierData   = $topSuppliers->pluck('purchases_sum_total_amount');

        // ── Recent purchases ──────────────────────────────────────
        $recentPurchases = Purchase::with('supplier')
            ->orderByDesc('purchase_date')
            ->limit(8)
            ->get();

        // ── Supplier with most orders this month ──────────────────
        $topThisMonth = Supplier::withCount(['purchases' => function ($q) {
            $q->where('purchase_date', '>=', Carbon::now()->startOfMonth());
        }])
        ->orderByDesc('purchases_count')
        ->first();

        return view('dashboard.suppliers', compact(
            'totalSuppliers', 'activeSuppliers', 'totalSpent',
            'thisMonthSpent', 'totalOrders', 'avgOrderValue',
            'topSuppliers', 'recentPurchases', 'topThisMonth',
            'monthlyLabels', 'monthlyData',
            'topSupplierLabels', 'topSupplierData'
        ));
    }
}
