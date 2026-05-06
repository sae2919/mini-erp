<?php

namespace App\Http\Controllers;

use App\Models\DispatchOrder;
use App\Models\StockRequest;
use App\Models\SellerStock;
use App\Models\Product;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StockRequestController extends Controller
{
    public function create()
    {
        $products = Product::where('stock_quantity', '>', 0)->get();

        $productsJs = $products->map(fn($p) => [
            'id'    => $p->id,
            'name'  => $p->name,
            'stock' => $p->stock_quantity,
        ]);

        return view('stock-requests.create', compact('products', 'productsJs'));
    }

    // ─────────────────────────────────────────────────────────────
    // STORE — now accepts multiple items at once
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
        ]);

        $seller = Seller::where('user_id', auth()->id())->firstOrFail();

        // ✅ 🔥 STOCK VALIDATION ADDED HERE
        foreach ($request->items as $item) {

            $product = Product::find($item['product_id']);

            if (!$product) {
                return back()->with('error', 'Product not found');
            }

            if ($item['quantity'] > $product->stock_quantity) {
                return back()->with('error',
                    "❌ Insufficient stock for {$product->name}. Only {$product->stock_quantity} available."
                );
            }
        }

        foreach ($request->items as $item) {
            StockRequest::create([
                'seller_id'      => $seller->id,
                'product_id'     => $item['product_id'],
                'quantity'       => $item['quantity'],
                'status'         => 'pending',
                'payment_status' => 'pending',
            ]);
        }

        $count = count($request->items);
        $msg   = $count === 1
            ? 'Stock request sent!'
            : "{$count} stock requests sent!";

        return back()->with('success', $msg);
    }

    // ─────────────────────────────────────────────────────────────
    // APPROVE
    // ─────────────────────────────────────────────────────────────
    public function approve($id)
    {
        $request = StockRequest::findOrFail($id);

        $exists = DispatchOrder::where('stock_request_id', $request->id)->exists();
        if ($exists) {
            return back()->with('error', 'Dispatch already created');
        }

        $request->update(['status' => 'approved']);

        $product = Product::findOrFail($request->product_id);

        if ($product->stock_quantity < $request->quantity) {
            return back()->with('error', 'Not enough stock available!');
        }

        $product->decrement('stock_quantity', $request->quantity);

        if (function_exists('logStock')) {
            logStock(
                $product->id,
                'out',
                $request->quantity,
                'dispatch',
                $request->id,
                'Stock dispatched'
            );
        }

        $total = ($product->price ?? 0) * $request->quantity;

        $dispatch = DispatchOrder::create([
            'seller_id'        => $request->seller_id,
            'user_id'          => auth()->id(),
            'reference'        => 'TEMP',
            'dispatch_date'    => now(),
            'total_amount'     => $total,
            'paid_amount'      => $total,
            'balance_amount'   => 0,
            'payment_status'   => 'paid',
            'status'           => 'dispatched',
            'stock_request_id' => $request->id,
        ]);

        $dispatch->update([
            'reference' => 'DSP-' . str_pad($dispatch->id, 6, '0', STR_PAD_LEFT),
        ]);

        \App\Models\DispatchItem::create([
            'dispatch_order_id' => $dispatch->id,
            'product_id'        => $product->id,
            'quantity'          => $request->quantity,
            'dispatch_price'    => $product->price,
        ]);

        return back()->with('success', 'Approved, Stock Updated & Dispatch Created');
    }

    // ─────────────────────────────────────────────────────────────
    // REJECT
    // ─────────────────────────────────────────────────────────────
    public function reject($id)
    {
        $req = StockRequest::findOrFail($id);
        $req->update(['status' => 'rejected']);

        return back()->with('success', 'Request rejected.');
    }

    // ─────────────────────────────────────────────────────────────
    // MY REQUESTS (seller view)
    // ─────────────────────────────────────────────────────────────
    // StockRequestController::myRequests()
// Totals pulled via DB queries (not collection) so they stay accurate across pages
public function myRequests()
{
    $seller = Seller::where('user_id', auth()->id())->firstOrFail();

    $requests = StockRequest::with('product')
        ->where('seller_id', $seller->id)
        ->latest()
        ->paginate(10)              // ← was get()
        ->withQueryString();

    // ── Totals calculated separately so they cover ALL requests, not just page 1 ──
    $allRequests   = StockRequest::with('product')->where('seller_id', $seller->id)->get();
    $totalAmount   = $allRequests->sum(fn($r) => ($r->product->price ?? 0) * $r->quantity);
    $paidAmount    = $allRequests->where('payment_status', 'paid')
                                 ->sum(fn($r) => ($r->product->price ?? 0) * $r->quantity);
    $pendingAmount = $totalAmount - $paidAmount;

    return view('stock-requests.my', compact(
        'requests', 'totalAmount', 'paidAmount', 'pendingAmount'
    ));
}

    // ─────────────────────────────────────────────────────────────
    // ADMIN INDEX
    // ─────────────────────────────────────────────────────────────
    public function adminIndex()
{
    $requests = StockRequest::with('product', 'seller')
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('stock_requests.admin', compact('requests'));
}

public function index()
{
    $requests = StockRequest::with('product', 'seller')
        ->latest()
        ->paginate(10)
        ->withQueryString();

    return view('stock_requests.admin', compact('requests'));
}
    // ─────────────────────────────────────────────────────────────
    // PAY
    // ─────────────────────────────────────────────────────────────
    public function pay(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:online,offline',
        ]);

        $seller = Seller::where('user_id', auth()->id())->firstOrFail();
        $req    = StockRequest::findOrFail($id);

        if ($req->seller_id != $seller->id) {
            abort(403, 'You are not allowed to pay this request');
        }

        if ($req->status !== 'approved') {
            return back()->with('error', 'Request not approved yet');
        }

        if ($req->payment_status === 'paid') {
            return back()->with('error', 'Already paid');
        }

        DB::transaction(function () use ($req, $request) {
            $req->update([
                'payment_status' => 'paid',
                'payment_method' => $request->payment_method,
            ]);

            SellerStock::updateOrCreate(
                ['seller_id'  => $req->seller_id, 'product_id' => $req->product_id],
                ['quantity'   => DB::raw('quantity + ' . $req->quantity)]
            );
        });

        return back()->with('success', 'Payment recorded & stock added!');
    }
}