<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BestProductsExport implements FromCollection, WithHeadings
{
    protected $reportService;

    public function __construct()
    {
        $this->reportService = app(ReportService::class);
    }

    public function collection()
    {
        $rows = $this->reportService->getTopSellingProducts();

        return collect($rows)->map(function ($row, $index) {
            return [
                $index + 1,
                $row->name,
                '-', // category not in query
                $row->total_qty,
                $row->total_revenue,
                '-', // commission (not in service)
                '-', // orders
                '-', // share %
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Product',
            'Category',
            'Units Sold',
            'Revenue',
            'Commission',
            'Orders',
            'Share %',
        ];
    }
}