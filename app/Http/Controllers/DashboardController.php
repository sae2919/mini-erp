<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\Expense;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Services\ReportService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function index()
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        // ── KPIs ──────────────────────────────────────────────────
        $totalProducts    = Product::active()->count();
        $totalSalesValue  = Sale::sum('total_amount');
        $totalPurchaseCost= Purchase::sum('total_amount');
        $lowStockCount    = Product::active()->lowStock()->count();

        // ── This month vs last month ──────────────────────────────
        $salesThisMonth  = Sale::where('sale_date', '>=', $thisMonth)->sum('total_amount');
        $salesLastMonth  = Sale::whereBetween('sale_date', [$lastMonth, $lastMonthEnd])->sum('total_amount');
        $salesGrowth     = $salesLastMonth > 0 ? round((($salesThisMonth - $salesLastMonth) / $salesLastMonth) * 100, 1) : 0;

        $purchasesThisMonth = Purchase::where('purchase_date', '>=', $thisMonth)->sum('total_amount');
        $expensesThisMonth  = Expense::where('expense_date', '>=', $thisMonth)->sum('amount');
        $salesToday         = Sale::whereDate('sale_date', $today)->sum('total_amount');

        // ── P&L Summary ───────────────────────────────────────────
        $grossProfit    = Sale::join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->selectRaw('SUM((sale_items.selling_price - sale_items.cost_price) * sale_items.quantity) as profit')
            ->whereNull('sales.deleted_at')
            ->value('profit') ?? 0;

        $netProfit      = $grossProfit - Expense::sum('amount');
        $profitMargin   = $totalSalesValue > 0 ? round(($grossProfit / $totalSalesValue) * 100, 1) : 0;

        // ── Receivables ───────────────────────────────────────────
        $totalReceivable  = Sale::whereIn('payment_status', ['unpaid','partial'])->sum('total_amount');
        $paymentsReceived = Payment::sum('amount');
        $outstandingAmount = $totalReceivable - $paymentsReceived;

        // ── 30-day sales trend ────────────────────────────────────
        $last30 = collect(range(29, 0))->map(function ($d) {
            $date = Carbon::today()->subDays($d);
            return [
                'date'  => $date->format('d M'),
                'total' => Sale::whereDate('sale_date', $date)->sum('total_amount'),
            ];
        });

        // ── 12-month revenue vs expenses ──────────────────────────
        $last12Months = collect(range(11, 0))->map(function ($m) {
            $date = Carbon::now()->startOfMonth()->subMonths($m);
            return [
                'month'    => $date->format('M Y'),
                'revenue'  => Sale::whereYear('sale_date', $date->year)->whereMonth('sale_date', $date->month)->sum('total_amount'),
                'expenses' => Expense::whereYear('expense_date', $date->year)->whereMonth('expense_date', $date->month)->sum('amount'),
                'purchases'=> Purchase::whereYear('purchase_date', $date->year)->whereMonth('purchase_date', $date->month)->sum('total_amount'),
            ];
        });

        // ── Top products ──────────────────────────────────────────
        $topProducts = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')
            ->select(['products.name', DB::raw('SUM(sale_items.subtotal) as revenue'), DB::raw('SUM(sale_items.quantity) as units')])
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('revenue')
            ->limit(6)->get();

        // ── Low stock + recent sales ──────────────────────────────
        $lowStockProducts = Product::active()->lowStock()->with('category')->orderBy('stock_quantity')->get();
        $recentSales      = Sale::with('items')->latest()->take(5)->get();
        $pendingOrders    = Sale::where('status', 'pending')->count();
        $unreadNotifications = ErpNotification::forUser(auth()->id())->unread()->latest()->take(5)->get();

        return view('dashboard.index', compact(
            'totalProducts', 'totalSalesValue', 'totalPurchaseCost', 'lowStockCount',
            'salesThisMonth', 'salesLastMonth', 'salesGrowth',
            'purchasesThisMonth', 'expensesThisMonth', 'salesToday',
            'grossProfit', 'netProfit', 'profitMargin',
            'totalReceivable', 'outstandingAmount',
            'last30', 'last12Months', 'topProducts',
            'lowStockProducts', 'recentSales', 'pendingOrders',
            'unreadNotifications'
        ));
    }
}
