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

        $sellerIds = $this->request->seller_ids ?? [];
        $columns   = $this->request->columns ?? [];

        $rows = DB::table('sellers')

            ->leftJoin(
                'dispatch_orders',
                'dispatch_orders.seller_id',
                '=',
                'sellers.id'
            )

            ->leftJoin(
                'seller_sales',
                'seller_sales.seller_id',
                '=',
                'sellers.id'
            )

            ->when($from, function ($q) use ($from) {

                $q->whereDate(
                    'dispatch_orders.created_at',
                    '>=',
                    $from
                );
            })

            ->when($to, function ($q) use ($to) {

                $q->whereDate(
                    'dispatch_orders.created_at',
                    '<=',
                    $to
                );
            })

            ->select([

                'sellers.id',
                'sellers.name as seller_name',
                'sellers.region',

                DB::raw('
                    COALESCE(
                        SUM(dispatch_orders.total_amount),
                    0)
                    as dispatched
                '),

                DB::raw('
                    COALESCE(
                        SUM(dispatch_orders.paid_amount),
                    0)
                    as collected
                '),

                DB::raw('
                    COALESCE(
                        SUM(dispatch_orders.total_amount)
                        -
                        SUM(dispatch_orders.paid_amount),
                    0)
                    as outstanding
                '),

                DB::raw('
                    COALESCE(
                        SUM(seller_sales.total_amount),
                    0)
                    as sales
                '),

                DB::raw('
                    COALESCE(
                        SUM(seller_sales.commission_amount),
                    0)
                    as commission
                '),

                DB::raw('
                    COALESCE(
                        SUM(dispatch_orders.total_amount)
                        -
                        SUM(dispatch_orders.paid_amount),
                    0)
                    as balance
                '),

            ])

            ->when(!empty($sellerIds), function ($q) use ($sellerIds) {

                $q->whereIn(
                    'sellers.id',
                    $sellerIds
                );
            })

            ->groupBy(
                'sellers.id',
                'sellers.name',
                'sellers.region'
            )

            ->get();

        return $rows->map(function ($row, $index) use ($columns) {

            $result = [];

            /*
            |--------------------------------------------------------------------------
            | ALWAYS INDEX
            |--------------------------------------------------------------------------
            */

            $result[] = $index + 1;

            /*
            |--------------------------------------------------------------------------
            | DYNAMIC COLUMNS
            |--------------------------------------------------------------------------
            */

            if (in_array('seller', $columns)) {

                $result[] = $row->seller_name;
            }

            if (in_array('region', $columns)) {

                $result[] = $row->region;
            }

            if (in_array('dispatched', $columns)) {

                $result[] = number_format(
                    $row->dispatched,
                    2
                );
            }

            if (in_array('collected', $columns)) {

                $result[] = number_format(
                    $row->collected,
                    2
                );
            }

            if (in_array('outstanding', $columns)) {

                $result[] = number_format(
                    $row->outstanding,
                    2
                );
            }

            if (in_array('sales', $columns)) {

                $result[] = number_format(
                    $row->sales,
                    2
                );
            }

            if (in_array('commission', $columns)) {

                $result[] = number_format(
                    $row->commission,
                    2
                );
            }

            if (in_array('balance', $columns)) {

                $result[] = number_format(
                    $row->balance,
                    2
                );
            }

            return $result;
        });
    }

    public function headings(): array
    {
        $columns = $this->request->columns ?? [];

        $headings = ['#'];

        if (in_array('seller', $columns)) {

            $headings[] = 'Seller';
        }

        if (in_array('region', $columns)) {

            $headings[] = 'Region';
        }

        if (in_array('dispatched', $columns)) {

            $headings[] = 'Dispatched';
        }

        if (in_array('collected', $columns)) {

            $headings[] = 'Collected';
        }

        if (in_array('outstanding', $columns)) {

            $headings[] = 'Outstanding';
        }

        if (in_array('sales', $columns)) {

            $headings[] = 'Sales';
        }

        if (in_array('commission', $columns)) {

            $headings[] = 'Commission';
        }

        if (in_array('balance', $columns)) {

            $headings[] = 'Balance';
        }

        return $headings;
    }
}