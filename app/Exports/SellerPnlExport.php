<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SellerPnlExport implements FromCollection, WithHeadings
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

        $rows = DB::table('seller_sales')
            ->join('sellers', 'sellers.id', '=', 'seller_sales.seller_id')
            ->when($from, fn($q) => $q->whereDate('seller_sales.sale_date', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('seller_sales.sale_date', '<=', $to))
            ->select([
                'sellers.name as seller_name',
                DB::raw('SUM(seller_sales.total_amount) as revenue'),
                DB::raw('COUNT(seller_sales.id) as orders'),
            ])
            ->groupBy('sellers.name')
            ->get();

        return $rows->map(function ($row, $index) {
            return [
                $index + 1,
                $row->seller_name,
                number_format($row->revenue, 2),
                $row->orders,
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'Seller',
            'Revenue',
            'Orders',
        ];
    }
}