<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    public function index(Request $request)
    {
        $commissions = Commission::with(['seller','sellerSale.items.product'])
            ->when($request->seller_id, fn($q) => $q->where('seller_id', $request->seller_id))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->when($request->from,      fn($q) => $q->whereHas('sellerSale', fn($q2) => $q2->whereDate('sale_date', '>=', $request->from)))
            ->when($request->to,        fn($q) => $q->whereHas('sellerSale', fn($q2) => $q2->whereDate('sale_date', '<=', $request->to)))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $sellers        = Seller::active()->orderBy('name')->get();
        $totalPending   = Commission::where('status','pending')->sum('amount');
        $totalPaid      = Commission::where('status','paid')->sum('amount');
        $totalAll       = Commission::sum('amount');

        return view('commissions.index', compact(
            'commissions','sellers','totalPending','totalPaid','totalAll'
        ));
    }

    // Mark single commission as paid
    public function markPaid(Commission $commission)
    {
        $commission->update(['status' => 'paid', 'paid_at' => now()->toDateString()]);
        return back()->with('success', "Commission ₹{$commission->amount} marked as paid.");
    }

    // Mark all pending commissions for a seller as paid
    public function payoutSeller(Request $request)
    {
        $request->validate(['seller_id' => 'required|exists:sellers,id']);

        $count = Commission::where('seller_id', $request->seller_id)
            ->where('status', 'pending')
            ->update(['status' => 'paid', 'paid_at' => now()->toDateString()]);

        $seller = Seller::find($request->seller_id);

        return back()->with('success', "Paid {$count} commission(s) to {$seller->name}.");
    }

    // Bulk mark all pending as paid
    public function payoutAll()
    {
        $count = Commission::where('status','pending')
            ->update(['status' => 'paid', 'paid_at' => now()->toDateString()]);

        return back()->with('success', "Marked {$count} commissions as paid.");
    }
}
