<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\ActivityLogger;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function download(Sale $sale)
    {
        $sale->load(['items.product.category', 'customer']);

        $pdf = Pdf::loadView('pdf.invoice', compact('sale'))
            ->setPaper('a4', 'portrait');

        ActivityLogger::log(
            'exported', 'Sale',
            "Invoice PDF downloaded for {$sale->reference}",
            $sale->id
        );

        return $pdf->download("invoice-{$sale->reference}.pdf");
    }

    public function preview(Sale $sale)
    {
        $sale->load(['items.product.category', 'customer']);

        $pdf = Pdf::loadView('pdf.invoice', compact('sale'))
            ->setPaper('a4', 'portrait');

        return $pdf->stream("invoice-{$sale->reference}.pdf");
    }
}
