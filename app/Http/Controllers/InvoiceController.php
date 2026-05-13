<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    // Download Invoice PDF
    public function download(Sale $sale)
    {
        $sale->load([
            'items.product.category',
            'customer'
        ]);

        $pdf = Pdf::loadView(
            'pdf.invoice',
            compact('sale')
        )->setPaper('a4', 'portrait');

        ActivityLogger::log(
            'exported',
            'Sale',
            "Invoice PDF downloaded for {$sale->reference}",
            $sale->id
        );

        return $pdf->download(
            "invoice-{$sale->reference}.pdf"
        );
    }

    // Preview Invoice PDF
    public function preview(Sale $sale)
    {
        $sale->load([
            'items.product.category',
            'customer'
        ]);

        $pdf = Pdf::loadView(
            'pdf.invoice',
            compact('sale')
        )->setPaper('a4', 'portrait');

        ActivityLogger::log(
            'previewed',
            'Sale',
            "Invoice PDF previewed for {$sale->reference}",
            $sale->id
        );

        return $pdf->stream(
            "invoice-{$sale->reference}.pdf"
        );
    }
}