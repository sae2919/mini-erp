<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

            // Summary row
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

    // ─── Helper ───────────────────────────────────────────────────

    private function streamCsv(string $filename, callable $callback): StreamedResponse
    {
        return response()->streamDownload($callback, "{$filename}.csv", [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}.csv\"",
        ]);
    }
}
