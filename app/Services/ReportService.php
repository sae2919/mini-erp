<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    // ─── Dashboard Summary ────────────────────────────────────────────────────

    public function getDashboardStats(): array
    {
        $today     = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        return [
            'total_products'     => Product::active()->count(),
            'total_sales_value'  => Sale::sum('total_amount'),
            'total_purchase_cost'=> Purchase::sum('total_amount'),
            'low_stock_count'    => Product::active()->lowStock()->count(),
            'low_stock_products' => Product::active()->lowStock()
                                        ->with('category')
                                        ->orderBy('stock_quantity')
                                        ->get(),
            'sales_today'        => Sale::whereDate('sale_date', $today)->sum('total_amount'),
            'sales_this_month'   => Sale::where('sale_date', '>=', $thisMonth)->sum('total_amount'),
            'purchases_this_month' => Purchase::where('purchase_date', '>=', $thisMonth)->sum('total_amount'),
            'recent_sales'       => Sale::with('items')->latest()->take(5)->get(),
        ];
    }

    // ─── Sales Report ─────────────────────────────────────────────────────────

    /**
     * @return array{sales: Collection, summary: array}
     */
    public function getSalesReport(?string $from, ?string $to): array
    {
        $sales = Sale::with(['items.product'])
            ->dateRange($from, $to)
            ->orderByDesc('sale_date')
            ->get();

        $summary = [
            'total_invoices'  => $sales->count(),
            'total_revenue'   => $sales->sum('total_amount'),
            'total_profit'    => $sales->sum(fn($s) => $s->totalProfit()),
            'avg_order_value' => $sales->count() ? $sales->avg('total_amount') : 0,
        ];

        return compact('sales', 'summary');
    }

    // ─── Purchase Report ──────────────────────────────────────────────────────

    public function getPurchaseReport(?string $from, ?string $to): array
    {
        $purchases = Purchase::with(['items.product', 'supplier'])
            ->dateRange($from, $to)
            ->orderByDesc('purchase_date')
            ->get();

        $summary = [
            'total_orders'   => $purchases->count(),
            'total_spent'    => $purchases->sum('total_amount'),
            'total_units'    => $purchases->flatMap->items->sum('quantity'),
        ];

        return compact('purchases', 'summary');
    }

    // ─── Profit Report ────────────────────────────────────────────────────────

    public function getProfitReport(?string $from, ?string $to): array
    {
        $rows = DB::table('sale_items')
            ->join('products', 'products.id', '=', 'sale_items.product_id')
            ->join('sales',    'sales.id',    '=', 'sale_items.sale_id')
            ->whereNull('sales.deleted_at')
            ->when($from, fn($q) => $q->whereDate('sales.sale_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('sales.sale_date', '<=', $to))
            ->select([
                'products.id',
                'products.name as product_name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_units_sold'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.cost_price * sale_items.quantity) as total_cost'),
                DB::raw('SUM((sale_items.selling_price - sale_items.cost_price) * sale_items.quantity) as total_profit'),
            ])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_profit')
            ->get();

        $summary = [
            'total_revenue' => $rows->sum('total_revenue'),
            'total_cost'    => $rows->sum('total_cost'),
            'total_profit'  => $rows->sum('total_profit'),
            'margin_pct'    => $rows->sum('total_revenue') > 0
                ? round(($rows->sum('total_profit') / $rows->sum('total_revenue')) * 100, 2)
                : 0,
        ];

        return ['rows' => $rows, 'summary' => $summary];
    }

    // ─── Product-wise Sales ───────────────────────────────────────────────────

    public function getTopSellingProducts()
{
    return DB::table('sale_items')

        ->join('products', 'products.id', '=', 'sale_items.product_id')

        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')

        ->join('sales', 'sales.id', '=', 'sale_items.sale_id')

        ->whereNull('sales.deleted_at')

        ->select(

            'products.id as product_id',

            'products.name',

            'categories.name as category_name',

            DB::raw('SUM(sale_items.quantity) as total_qty'),

            DB::raw('SUM(sale_items.subtotal) as total_revenue'),

            DB::raw('SUM(sale_items.commission_amount) as total_commission'),

            DB::raw('COUNT(DISTINCT sales.id) as order_count')

        )

        ->groupBy(
            'products.id',
            'products.name',
            'categories.name'
        )

        ->orderByDesc('total_qty')

        ->limit(10)

        ->get();
}
    public function getStockMovementReport($from = null, $to = null, $categoryId = null)
{
    $query = DB::table('products')
        ->leftJoin('categories', 'categories.id', '=', 'products.category_id');

    // ✅ FILTER: DATE (if you use created_at or production date)
    if ($from && $to) {
        $query->whereBetween('products.created_at', [$from, $to]);
    }

    // ✅ FILTER: CATEGORY
    if ($categoryId) {
        $query->where('products.category_id', $categoryId);
    }

    return $query->select(
        'products.name as product_name',
        'categories.name as category_name',
        'products.stock_quantity as total_stock'
    )->get();
}
}
