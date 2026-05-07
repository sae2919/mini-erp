<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockReportExport implements FromCollection, WithHeadings
{
    protected $data;
    protected $columns;

    public function __construct($data, $columns)
    {
        $this->data = $data;
        $this->columns = $columns;
    }

    public function collection()
    {
        return collect($this->data)->map(function ($row) {

            $result = [];

            foreach ($this->columns as $col) {

                switch ($col) {

                    case 'product':
                        $result[] = $row->product_name;
                        break;

                    case 'category':
                        $result[] = $row->category_name;
                        break;

                    case 'produced':
                        $result[] = $row->produced;
                        break;

                    case 'dispatched':
                        $result[] = $row->dispatched;
                        break;

                    case 'sold':
                        $result[] = $row->sold;
                        break;

                    case 'warehouse':
                        $result[] = $row->warehouse;
                        break;

                    case 'with_sellers':
                        $result[] = $row->with_sellers;
                        break;

                    case 'total_stock':
                        $result[] = $row->total_stock;
                        break;
                }
            }

            return $result;
        });
    }

    public function headings(): array
    {
        $headers = [];

        foreach ($this->columns as $col) {

            switch ($col) {

                case 'product':
                    $headers[] = 'Product';
                    break;

                case 'category':
                    $headers[] = 'Category';
                    break;

                case 'produced':
                    $headers[] = 'Produced';
                    break;

                case 'dispatched':
                    $headers[] = 'Dispatched';
                    break;

                case 'sold':
                    $headers[] = 'Sold';
                    break;

                case 'warehouse':
                    $headers[] = 'Warehouse';
                    break;

                case 'with_sellers':
                    $headers[] = 'With Sellers';
                    break;

                case 'total_stock':
                    $headers[] = 'Total Stock';
                    break;
            }
        }

        return $headers;
    }
}