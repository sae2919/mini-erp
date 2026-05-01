<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reportService) {}

    public function sales(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->reportService->getSalesReport($request->from, $request->to);
        return view('reports.sales', $data + ['from' => $request->from, 'to' => $request->to]);
    }

    public function purchases(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->reportService->getPurchaseReport($request->from, $request->to);
        return view('reports.purchases', $data + ['from' => $request->from, 'to' => $request->to]);
    }

    public function profit(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $data = $this->reportService->getProfitReport($request->from, $request->to);
        return view('reports.profit', $data + ['from' => $request->from, 'to' => $request->to]);
    }
}
