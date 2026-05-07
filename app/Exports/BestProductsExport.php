<?php

namespace App\Exports;

use App\Services\ReportService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BestProductsExport implements FromCollection, WithHeadings
{
    protected $reportService;
    protected $request;

    public function __construct($request)
    {
        $this->reportService = app(ReportService::class);
        $this->request = $request;
    }

    public function collection()
    {
        $columns = $this->request->columns ?? [];
        $productIds = $this->request->product_ids ?? [];

        $rows = $this->reportService->getTopSellingProducts();

        // FILTER SELECTED PRODUCTS

        if (!empty($productIds)) {

            $rows = collect($rows)->filter(function ($row) use ($productIds) {

                return in_array($row->product_id, $productIds);

            });

        }

        $totalRevenue = collect($rows)->sum('total_revenue');

        return collect($rows)->values()->map(function ($row, $index) use ($columns, $totalRevenue) {

            $result = [];

            // ALWAYS INDEX

            $result[] = $index + 1;

            // PRODUCT

            if (empty($columns) || in_array('product', $columns)) {

                $result[] = $row->name;

            }

            // CATEGORY

            if (empty($columns) || in_array('category', $columns)) {

                $result[] = $row->category_name ?? '-';

            }

            // UNITS SOLD

            if (empty($columns) || in_array('units', $columns)) {

                $result[] = $row->total_qty;

            }

            // REVENUE

            if (empty($columns) || in_array('revenue', $columns)) {

                $result[] = number_format($row->total_revenue, 2);

            }

            // COMMISSION

            if (empty($columns) || in_array('commission', $columns)) {

                $result[] = number_format($row->total_commission ?? 0, 2);

            }

            // ORDERS

            if (empty($columns) || in_array('orders', $columns)) {

                $result[] = $row->order_count ?? 0;

            }

            // SHARE %

            if (empty($columns) || in_array('share', $columns)) {

                $share = $totalRevenue > 0
                    ? round(($row->total_revenue / $totalRevenue) * 100, 1)
                    : 0;

                $result[] = $share . '%';

            }

            return $result;
        });
    }

    public function headings(): array
    {
        $columns = $this->request->columns ?? [];

        $headings = ['#'];

        if (empty($columns) || in_array('product', $columns)) {

            $headings[] = 'Product';

        }

        if (empty($columns) || in_array('category', $columns)) {

            $headings[] = 'Category';

        }

        if (empty($columns) || in_array('units', $columns)) {

            $headings[] = 'Units Sold';

        }

        if (empty($columns) || in_array('revenue', $columns)) {

            $headings[] = 'Revenue';

        }

        if (empty($columns) || in_array('commission', $columns)) {

            $headings[] = 'Commission';

        }

        if (empty($columns) || in_array('orders', $columns)) {

            $headings[] = 'Orders';

        }

        if (empty($columns) || in_array('share', $columns)) {

            $headings[] = 'Share %';

        }

        return $headings;
    }
}