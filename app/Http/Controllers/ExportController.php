<?php

namespace App\Http\Controllers;

use App\Exports\StockReportExport;
use App\Exports\BestProductsExport;
use App\Exports\SellerPnlExport;
use App\Exports\SellerPerformanceExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\ActivityLogger;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;


class ExportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function sales(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $data = $this->reportService->getSalesReport($request->from, $request->to);

        ActivityLogger::exported('Sale', "Sales report exported" . ($request->from ? " from {$request->from} to {$request->to}" : ""));

        return $this->streamCsv("sales-report-" . now()->format('Y-m-d'), function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Reference', 'Date', 'Customer', 'Items', 'Revenue (₹)', 'Profit (₹)']);

            foreach ($data['sales'] as $sale) {
                fputcsv($handle, [
                    $sale->reference,
                    $sale->sale_date->format('d-m-Y'),
                    $sale->customer_display,
                    $sale->items->count(),
                    number_format($sale->total_amount, 2),
                    number_format($sale->totalProfit(), 2),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL', '', '', $data['summary']['total_invoices'],
                number_format($data['summary']['total_revenue'], 2),
                number_format($data['summary']['total_profit'], 2),
            ]);

            fclose($handle);
        });
    }

    public function purchases(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $data = $this->reportService->getPurchaseReport($request->from, $request->to);

        ActivityLogger::exported('Purchase', "Purchase report exported");

        return $this->streamCsv("purchase-report-" . now()->format('Y-m-d'), function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Reference', 'Date', 'Supplier', 'Items', 'Total Cost (₹)']);

            foreach ($data['purchases'] as $purchase) {
                fputcsv($handle, [
                    $purchase->reference,
                    $purchase->purchase_date->format('d-m-Y'),
                    $purchase->supplier->name,
                    $purchase->items->count(),
                    number_format($purchase->total_amount, 2),
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL', '', '', $data['summary']['total_orders'],
                number_format($data['summary']['total_spent'], 2),
            ]);

            fclose($handle);
        });
    }

    public function profit(Request $request): StreamedResponse
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date'],
        ]);

        $data = $this->reportService->getProfitReport($request->from, $request->to);

        ActivityLogger::exported('Profit', "Profit report exported");

        return $this->streamCsv("profit-report-" . now()->format('Y-m-d'), function () use ($data) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Product', 'SKU', 'Units Sold', 'Revenue (₹)', 'Cost (₹)', 'Profit (₹)', 'Margin %']);

            foreach ($data['rows'] as $row) {
                $margin = $row->total_revenue > 0
                    ? round(($row->total_profit / $row->total_revenue) * 100, 1) : 0;

                fputcsv($handle, [
                    $row->product_name,
                    $row->sku,
                    $row->total_units_sold,
                    number_format($row->total_revenue, 2),
                    number_format($row->total_cost, 2),
                    number_format($row->total_profit, 2),
                    $margin . '%',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['TOTAL', '', '',
                number_format($data['summary']['total_revenue'], 2),
                number_format($data['summary']['total_cost'], 2),
                number_format($data['summary']['total_profit'], 2),
                $data['summary']['margin_pct'] . '%',
            ]);

            fclose($handle);
        });
    }

    private function streamCsv(string $filename, callable $callback): StreamedResponse
    {
        return response()->streamDownload(function () use ($callback) {

            while (ob_get_level() > 0) {
                ob_end_clean();
            }

            echo "\xEF\xBB\xBF";

            $callback();

        }, "{$filename}.csv", [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
            'Cache-Control'       => 'no-store, no-cache',
        ]);
    }

    public function stockExcel(Request $request)
{
    $from = $request->from;
    $to   = $request->to;

    $productIds = $request->product_ids ?? [];

    $columns = $request->columns ?? [];

    $query = DB::table('products')

        ->leftJoin('categories', 'categories.id', '=', 'products.category_id')

        ->select(

            'products.id as product_id',

            'products.name as product_name',

            'categories.name as category_name',

            'products.stock_quantity as warehouse',

            DB::raw('
                (
                    SELECT COALESCE(SUM(pi.quantity),0)
                    FROM production_items pi
                    WHERE pi.product_id = products.id
                ) as produced
            '),

            DB::raw('
                (
                    SELECT COALESCE(SUM(di.quantity),0)
                    FROM dispatch_items di
                    WHERE di.product_id = products.id
                ) as dispatched
            '),

            DB::raw('
                (
                    SELECT COALESCE(SUM(si.quantity),0)
                    FROM sale_items si
                    WHERE si.product_id = products.id
                ) as sold
            '),

            DB::raw('
                GREATEST(
                    0,
                    (
                        SELECT COALESCE(SUM(di.quantity),0)
                        FROM dispatch_items di
                        WHERE di.product_id = products.id
                    )
                    -
                    (
                        SELECT COALESCE(SUM(ss.quantity),0)
                        FROM seller_sale_items ss
                        WHERE ss.product_id = products.id
                    )
                ) as with_sellers
            '),

            DB::raw('
                (
                    products.stock_quantity
                    +
                    GREATEST(
                        0,
                        (
                            SELECT COALESCE(SUM(di.quantity),0)
                            FROM dispatch_items di
                            WHERE di.product_id = products.id
                        )
                        -
                        (
                            SELECT COALESCE(SUM(ss.quantity),0)
                            FROM seller_sale_items ss
                            WHERE ss.product_id = products.id
                        )
                    )
                ) as total_stock
            ')
        );

    // FILTER PRODUCTS
    if (!empty($productIds)) {
        $query->whereIn('products.id', $productIds);
    }

    // DATE FILTER
    if ($from && $to) {
        $query->whereBetween(
            'products.created_at',
            [
                $from . ' 00:00:00',
                $to . ' 23:59:59'
            ]
        );
    }

    $data = $query
        ->orderBy('products.name')
        ->get();

    return Excel::download(

        new StockReportExport($data, $columns),

        'stock-report.xlsx'
    );
}

    public function bestProductsExcel(Request $request)
    {
       return Excel::download(
    new BestProductsExport($request),
    'best-products.xlsx'
);
    }

    public function sellerPnlExcel(Request $request)
    {
        return Excel::download(new SellerPnlExport($request), 'seller-pnl.xlsx');
    }

    public function sellerPerformanceExcel(Request $request)
    {
        return Excel::download(new SellerPerformanceExport($request), 'seller-performance.xlsx');
    }
}