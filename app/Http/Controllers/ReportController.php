<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\DispatchOrder;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerPayment;
use App\Models\SellerSale;
use App\Models\SellerSaleItem;
use App\Models\SellerStock;
use App\Models\Production;
use App\Models\ProductionItem;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    // ════════════════════════════════════════════════════════════════
    //  CORE REPORTS  (routed, were missing — caused 500 errors)
    // ════════════════════════════════════════════════════════════════

    /**
     * GET /reports/sales
     * Accessible by: admin, viewer
     */
    public function sales(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        // Default to current month when no filter is supplied
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        ['sales' => $sales, 'summary' => $summary] =
            $this->reportService->getSalesReport($from, $to);

        return view('reports.sales', compact('sales', 'summary', 'from', 'to'));
    }

    /**
     * GET /reports/purchases
     * Accessible by: admin, inventory_manager, viewer
     */
    public function purchases(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        ['purchases' => $purchases, 'summary' => $summary] =
            $this->reportService->getPurchaseReport($from, $to);

        return view('reports.purchases', compact('purchases', 'summary', 'from', 'to'));
    }

    /**
     * GET /reports/profit
     * Accessible by: admin, viewer
     * Uses cost_price SNAPSHOT from sale_items — not the live product cost.
     */
    public function profit(Request $request)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        ['rows' => $rows, 'summary' => $summary] =
            $this->reportService->getProfitReport($from, $to);

        return view('reports.profit', compact('rows', 'summary', 'from', 'to'));
    }

    // ════════════════════════════════════════════════════════════════
    //  SELLER / DISPATCH REPORTS
    // ════════════════════════════════════════════════════════════════

    public function commission(Request $request)
    {
        $sellers = Seller::active()->orderBy('name')->get();

        $data = Seller::with('commissions')
            ->withSum(['commissions as total_commission' => fn($q) => $q], 'amount')
            ->withSum(['commissions as paid_commission'  => fn($q) => $q->where('status', 'paid')], 'amount')
            ->withSum(['commissions as pending_commission' => fn($q) => $q->where('status', 'pending')], 'amount')
            ->when($request->seller_id, fn($q) => $q->where('id', $request->seller_id))
            ->orderByDesc('total_commission')
            ->get();

        $totalAll     = Commission::sum('amount');
        $totalPaid    = Commission::where('status', 'paid')->sum('amount');
        $totalPending = Commission::where('status', 'pending')->sum('amount');

        return view('reports.commission', compact(
            'data', 'sellers', 'totalAll', 'totalPaid', 'totalPending'
        ));
    }

    public function sellerPnl(Request $request)
    {
        $sellers = Seller::active()->orderBy('name')->get();

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $data = Seller::with(['dispatchOrders', 'sales', 'payments', 'commissions'])
            ->when($request->seller_id, fn($q) => $q->where('id', $request->seller_id))
            ->get()
            ->map(function ($seller) use ($from, $to) {
                $dispatched = $seller->dispatchOrders()
                    ->whereBetween('dispatch_date', [$from, $to])
                    ->where('status', '!=', 'cancelled')
                    ->sum('total_amount');

                $collected = $seller->payments()
                    ->whereBetween('paid_at', [$from, $to])
                    ->sum('amount');

                $salesAmount = $seller->sales()
                    ->whereBetween('sale_date', [$from, $to])
                    ->sum('total_amount');

                $commission = $seller->commissions()
                    ->whereHas('sellerSale', fn($q) => $q->whereBetween('sale_date', [$from, $to]))
                    ->sum('amount');

                return [
                    'seller'      => $seller,
                    'dispatched'  => $dispatched,
                    'collected'   => $collected,
                    'outstanding' => max(0, $seller->balance_due),
                    'sales'       => $salesAmount,
                    'commission'  => $commission,
                    'net'         => $dispatched - $collected,
                ];
            });

        return view('reports.seller-pnl', compact('data', 'sellers', 'from', 'to'));
    }

    public function accountStatement(Request $request)
    {
        $request->validate(['seller_id' => 'required|exists:sellers,id']);

        $seller = Seller::findOrFail($request->seller_id);
        $from   = $request->from ?? now()->subMonths(3)->toDateString();
        $to     = $request->to   ?? now()->toDateString();

        $dispatches = $seller->dispatchOrders()
            ->with('items.product')
            ->whereBetween('dispatch_date', [$from, $to])
            ->where('status', '!=', 'cancelled')
            ->get()
            ->map(fn($d) => [
                'date'        => $d->dispatch_date,
                'type'        => 'dispatch',
                'description' => "Dispatch {$d->reference} ({$d->items->count()} products)",
                'debit'       => $d->total_amount,
                'credit'      => 0,
                'ref'         => $d->reference,
                'id'          => $d->id,
            ]);

        $payments = $seller->payments()
            ->whereBetween('paid_at', [$from, $to])
            ->get()
            ->map(fn($p) => [
                'date'        => $p->paid_at,
                'type'        => 'payment',
                'description' => 'Payment received via ' . ucfirst($p->method)
                    . ($p->reference ? " ({$p->reference})" : ''),
                'debit'       => 0,
                'credit'      => $p->amount,
                'ref'         => $p->reference,
                'id'          => $p->id,
            ]);

        $sales = $seller->sales()
            ->whereBetween('sale_date', [$from, $to])
            ->get()
            ->map(fn($s) => [
                'date'        => $s->sale_date,
                'type'        => 'sale',
                'description' => "Sale {$s->reference} to " . ($s->customer_name ?: 'Walk-in'),
                'debit'       => 0,
                'credit'      => 0,
                'sale_amount' => $s->total_amount,
                'commission'  => $s->commission_amount,
                'ref'         => $s->reference,
                'id'          => $s->id,
            ]);

        $transactions = $dispatches->concat($payments)->sortBy('date')->values();

        $totalDispatched  = $dispatches->sum('debit');
        $totalCollected   = $payments->sum('credit');
        $totalSales       = $sales->sum('sale_amount');
        $totalCommissions = $sales->sum('commission');
        $balance          = $totalDispatched - $totalCollected;

        $sellers = Seller::active()->orderBy('name')->get();

        return view('reports.account-statement', compact(
            'seller', 'transactions', 'sales',
            'totalDispatched', 'totalCollected', 'totalSales', 'totalCommissions', 'balance',
            'from', 'to', 'sellers'
        ));
    }

    public function bestProducts(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $products = SellerSaleItem::with('product.category')
            ->whereHas('sellerSale', fn($q) => $q->whereBetween('sale_date', [$from, $to]))
            ->selectRaw('product_id,
                SUM(quantity) as total_qty,
                SUM(subtotal) as total_revenue,
                SUM(commission_amount) as total_commission,
                COUNT(DISTINCT seller_sale_id) as order_count')
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->get();

        $totalRevenue = $products->sum('total_revenue');

        return view('reports.best-products', compact('products', 'from', 'to', 'totalRevenue'));
    }

    public function sellerPerformance(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $sellers = Seller::with([
            'sales'  => fn($q) => $q->whereBetween('sale_date', [$from, $to]),
            'stocks',
        ])
        ->get()
        ->map(function ($seller) use ($from, $to) {
            $sales      = $seller->sales()->whereBetween('sale_date', [$from, $to]);
            $dispatched = $seller->dispatchOrders()
                ->whereBetween('dispatch_date', [$from, $to])
                ->sum('total_amount');

            return [
                'seller'       => $seller,
                'sales_count'  => $sales->count(),
                'sales_amount' => $sales->sum('total_amount'),
                'commission'   => $sales->sum('commission_amount'),
                'dispatched'   => $dispatched,
                'stock_value'  => $seller->stocks()->sum('quantity'),
                'outstanding'  => max(0, $seller->balance_due),
            ];
        })
        ->sortByDesc('sales_amount')
        ->values();

        return view('reports.seller-performance', compact('sellers', 'from', 'to'));
    }

    public function stockMovement(Request $request)
    {
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        $products = Product::active()->with('category')
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->get()
            ->map(function ($product) use ($from, $to) {
                $produced = ProductionItem::whereHas(
                    'production',
                    fn($q) => $q->whereBetween('production_date', [$from, $to])
                )
                ->where('product_id', $product->id)
                ->sum('quantity');

                $dispatched = DB::table('dispatch_items')
                    ->join('dispatch_orders', 'dispatch_orders.id', '=', 'dispatch_items.dispatch_order_id')
                    ->where('dispatch_items.product_id', $product->id)
                    ->whereBetween('dispatch_orders.dispatch_date', [$from, $to])
                    ->where('dispatch_orders.status', '!=', 'cancelled')
                    ->whereNull('dispatch_orders.deleted_at')
                    ->sum('dispatch_items.quantity');

                $sold = DB::table('seller_sale_items')
                    ->join('seller_sales', 'seller_sales.id', '=', 'seller_sale_items.seller_sale_id')
                    ->where('seller_sale_items.product_id', $product->id)
                    ->whereBetween('seller_sales.sale_date', [$from, $to])
                    ->whereNull('seller_sales.deleted_at')
                    ->sum('seller_sale_items.quantity');

                $sellerStock = SellerStock::where('product_id', $product->id)->sum('quantity');

                return [
                    'product'      => $product,
                    'produced'     => $produced,
                    'dispatched'   => $dispatched,
                    'sold'         => $sold,
                    'warehouse'    => $product->stock_quantity,
                    'seller_stock' => $sellerStock,
                    'total_stock'  => $product->stock_quantity + $sellerStock,
                ];
            })
            ->filter(fn($p) =>
                $p['produced'] > 0 || $p['dispatched'] > 0
                || $p['sold'] > 0   || $p['total_stock'] > 0
            )
            ->values();

        $categories = \App\Models\Category::orderBy('name')->get();

        return view('reports.stock-movement', compact('products', 'from', 'to', 'categories'));
    }
}