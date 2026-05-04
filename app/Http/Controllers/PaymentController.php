<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Sale;
use App\Models\ErpNotification;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $payments = Payment::with(['sale', 'user'])
            ->when($request->method, fn($q) => $q->where('method', $request->method))
            ->when($request->from, fn($q) => $q->whereDate('paid_at', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->whereDate('paid_at', '<=', $request->to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalCollected = Payment::sum('amount');
        $todayCollected = Payment::whereDate('paid_at', today())->sum('amount');
        $unpaidSales    = Sale::where('payment_status', 'unpaid')->whereNotIn('status', ['cancelled'])->count();
        $overdueAmount  = Sale::where('payment_status', 'unpaid')
            ->whereDate('sale_date', '<', now()->subDays(30))
            ->sum(DB::raw('total_amount - (SELECT COALESCE(SUM(amount),0) FROM payments WHERE payments.sale_id = sales.id)'));

        return view('payments.index', compact(
            'payments', 'totalCollected', 'todayCollected', 'unpaidSales', 'overdueAmount'
        ));
    }

    public function store(Request $request, Sale $sale)
    {
        $validated = $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['required', 'in:cash,upi,card,bank_transfer,cheque'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at'   => ['required', 'date'],
            'notes'     => ['nullable', 'string', 'max:500'],
        ]);
        

        $totalPaid  = $sale->payments()->sum('amount');
        $remaining  = $sale->total_amount - $totalPaid;

        if ($validated['amount'] > $remaining + 0.01) {
            return back()->withErrors(['amount' => "Maximum payable is ₹" . number_format($remaining, 2)]);
        }

        DB::transaction(function () use ($validated, $sale, $totalPaid) {
            $payment = Payment::create([
                ...$validated,
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
            ]);

            // Update payment status on sale
            $newTotal = $totalPaid + $payment->amount;
            $status   = match(true) {
                $newTotal >= $sale->total_amount => 'paid',
                $newTotal > 0                    => 'partial',
                default                          => 'unpaid',
            };

            $sale->update(['payment_status' => $status]);

            ActivityLogger::log('payment_received', 'Payment',
                "Payment of ₹{$payment->amount} received for {$sale->reference} via {$payment->method}",
                $sale->id
            );

            if ($status === 'paid') {
                ErpNotification::notify('payment_received',
                    "Payment Complete — {$sale->reference}",
                    "₹{$sale->total_amount} fully received via {$payment->method}",
                    ['icon' => '💳', 'color' => 'green', 'url' => route('sales.show', $sale)]
                );
            }
        });

        return back()->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        $sale = $payment->sale;
        $payment->delete();

        // Recalculate payment status
        $totalPaid = $sale->payments()->sum('amount');
        $status = match(true) {
            $totalPaid >= $sale->total_amount => 'paid',
            $totalPaid > 0                    => 'partial',
            default                           => 'unpaid',
        };
        $sale->update(['payment_status' => $status]);

        return back()->with('success', 'Payment deleted and status updated.');
    }

    public function receivables(Request $request)
    {
        $unpaidSales = Sale::with(['customer', 'payments'])
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->whereNotIn('status', ['cancelled'])
            ->when($request->from, fn($q) => $q->whereDate('sale_date', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->whereDate('sale_date', '<=', $request->to))
            ->orderBy('sale_date')
            ->paginate(20)
            ->withQueryString();

        return view('payments.receivables', compact('unpaidSales'));
    }
}
