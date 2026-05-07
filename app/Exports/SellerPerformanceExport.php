<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SellerPerformanceExport implements FromCollection, WithHeadings
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function collection()
{
    $from = $this->request->from;
    $to   = $this->request->to;

    $sellerIds = $this->request->seller_ids ?? [];

    $columns = $this->request->columns ?? [];

    $rows = DB::table('seller_sales')
        ->join('sellers', 'sellers.id', '=', 'seller_sales.seller_id')

        ->when($from, function ($q) use ($from) {
            $q->whereDate('seller_sales.sale_date', '>=', $from);
        })

        ->when($to, function ($q) use ($to) {
            $q->whereDate('seller_sales.sale_date', '<=', $to);
        })

        ->when(!empty($sellerIds), function ($q) use ($sellerIds) {
            $q->whereIn('seller_sales.seller_id', $sellerIds);
        })

        ->select([
            'sellers.name as seller_name',
            DB::raw('COUNT(seller_sales.id) as total_orders'),
            DB::raw('SUM(seller_sales.total_amount) as revenue'),
        ])

        ->groupBy('sellers.id', 'sellers.name')

        ->get();

    return $rows->map(function ($row, $index) use ($columns) {

        $result = [];

        // SERIAL NUMBER
        $result[] = $index + 1;

        if (empty($columns) || in_array('seller', $columns)) {
            $result[] = $row->seller_name;
        }

        if (empty($columns) || in_array('sales', $columns)) {
            $result[] = $row->total_orders;
        }

        if (empty($columns) || in_array('revenue', $columns)) {
            $result[] = number_format($row->revenue, 2);
        }

        return $result;
    });
}

    public function headings(): array
    {
        $columns = $this->request->columns ?? [];

        $headings = ['#'];

        if (empty($columns) || in_array('seller', $columns)) {
            $headings[] = 'Seller';
        }

        if (empty($columns) || in_array('sales', $columns)) {
            $headings[] = 'Orders';
        }

        if (empty($columns) || in_array('revenue', $columns)) {
            $headings[] = 'Revenue';
        }

        return $headings;
    }
}