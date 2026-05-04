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
        return view('stock-requests.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $seller = Seller::where('user_id', auth()->id())->firstOrFail();

        StockRequest::create([
            'seller_id'      => $seller->id,
            'product_id'     => $request->product_id,
            'quantity'       => $request->quantity,
            'status'         => 'pending',
            'payment_status' => 'pending',
        ]);

        return back()->with('success', 'Stock request sent!');
    }

    public function approve($id)
    {
        $request = StockRequest::findOrFail($id);

        $exists = DispatchOrder::where('stock_request_id', $request->id)->exists();
        if ($exists) {
            return back()->with('error', 'Dispatch already created');
        }

        $request->update([
            'status' => 'approved'
        ]);

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
            'seller_id' => $request->seller_id,
            'user_id' => auth()->id(),
            'reference' => 'TEMP',
            'dispatch_date' => now(),
            'total_amount' => $total,
            'paid_amount' => $total,
            'balance_amount' => 0,
            'payment_status' => 'paid',
            'status' => 'dispatched',
            'stock_request_id' => $request->id
        ]);

        $dispatch->update([
            'reference' => 'DSP-' . str_pad($dispatch->id, 6, '0', STR_PAD_LEFT)
        ]);

        \App\Models\DispatchItem::create([
            'dispatch_order_id' => $dispatch->id,
            'product_id' => $product->id,
            'quantity' => $request->quantity,
            'dispatch_price' => $product->price,
        ]);

        return back()->with('success', 'Approved, Stock Updated & Dispatch Created');
    }

    public function reject($id)
    {
        $req = StockRequest::findOrFail($id);
        $req->update(['status' => 'rejected']);

        return back()->with('success', '❌ Request rejected.');
    }

    public function myRequests()
    {
        $seller = Seller::where('user_id', auth()->id())->firstOrFail();

        $requests = StockRequest::with('product')
            ->where('seller_id', $seller->id)
            ->latest()
            ->get();

        $totalAmount = $requests->sum(function ($r) {
            return ($r->product->price ?? 0) * $r->quantity;
        });

        $paidAmount = $requests->where('payment_status', 'paid')->sum(function ($r) {
            return ($r->product->price ?? 0) * $r->quantity;
        });

        $pendingAmount = $totalAmount - $paidAmount;

        return view('stock-requests.my', compact(
            'requests',
            'totalAmount',
            'paidAmount',
            'pendingAmount'
        ));
    }

    public function adminIndex()
    {
        $requests = StockRequest::with('product', 'seller')->latest()->get();
        return view('stock_requests.admin', compact('requests'));
    }

    public function index()
    {
        $requests = StockRequest::with('product', 'seller')->latest()->get();
        return view('stock_requests.admin', compact('requests'));
    }

    public function pay(Request $request, $id)
    {
        $request->validate([
            'payment_method' => 'required|in:online,offline',
        ]);

        $seller = Seller::where('user_id', auth()->id())->firstOrFail();

        $req = StockRequest::findOrFail($id);

        // 🔥 SECURITY FIX (no more 403)
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
                [
                    'seller_id'  => $req->seller_id,
                    'product_id' => $req->product_id,
                ],
                [
                    'quantity' => DB::raw('quantity + ' . $req->quantity),
                ]
            );
        });

        return back()->with('success', '✅ Payment recorded & stock added!');
    }
}