<?php

namespace App\Http\Controllers;
use App\Models\StockRequest;
use App\Models\SellerStock;
use App\Models\Product;
use App\Models\Seller;

use Illuminate\Support\Facades\DB;



use Illuminate\Http\Request;

class StockRequestController extends Controller
{

public function create()
{
    $products = \App\Models\Product::where('stock_quantity', '>', 0)->get();

    return view('stock-requests.create', compact('products'));
}
    public function store(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'required|integer|min:1'
    ]);

    $seller = Seller::where('user_id', auth()->id())->firstOrFail();

    StockRequest::create([
        'seller_id'  => $seller->id,
        'product_id' => $request->product_id,
        'quantity'   => $request->quantity,
    ]);

    return back()->with('success','Stock request sent!');
}


public function approve($id)
{
    $req = \App\Models\StockRequest::findOrFail($id);

    DB::transaction(function () use ($req) {

        // ✅ update request status
        $req->update(['status' => 'approved']);

        // ✅ add stock to seller
        SellerStock::updateOrCreate(
            [
                'seller_id' => $req->seller_id,
                'product_id'=> $req->product_id
            ],
            [
                'quantity' => DB::raw('quantity + '.$req->quantity)
            ]
        );
    });

    return back()->with('success', '✅ Stock approved & added to seller!');
}
public function reject($id)
{
    $req = \App\Models\StockRequest::findOrFail($id);

    $req->update(['status' => 'rejected']);

    return back()->with('success', '❌ Request rejected');
}
public function myRequests()
{
    $seller = \App\Models\Seller::where('user_id', auth()->id())->firstOrFail();

    $requests = \App\Models\StockRequest::with('product')
        ->where('seller_id', $seller->id)
        ->latest()
        ->get();

    return view('stock-requests.my', compact('requests'));
}
public function adminIndex()
{
    $requests = \App\Models\StockRequest::with('product','seller')
        ->latest()
        ->get();

    return view('stock_requests.admin', compact('requests'));
}
public function index()
{
    // Admin view (same as adminIndex)
    $requests = \App\Models\StockRequest::with('product','seller')
        ->latest()
        ->get();

    return view('stock_requests.admin', compact('requests'));
}
}
