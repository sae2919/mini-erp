<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReportExport implements FromCollection, WithHeadings
{
    protected $request;
    protected $reportService;

    public function __construct($request)
    {
        $this->request = $request;
        $this->reportService = app(ReportService::class);
    }

    public function collection()
    {
        $from = $this->request->from;
        $to   = $this->request->to;

        $data = $this->reportService->getStockMovementReport($from, $to);

        return collect($data)->map(function ($row) {
            return [
                $row->product_name,
                $row->category_name,
                $row->produced,
                $row->dispatched,
                $row->sold,
                $row->warehouse,
                $row->with_sellers,
                $row->total_stock,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product',
            'Category',
            'Produced',
            'Dispatched',
            'Sold',
            'Warehouse',
            'With Sellers',
            'Total Stock',
        ];
    }
}