<?php

namespace App\Http\Controllers;

use App\Models\Seller;

class SellerDispatchController extends Controller
{
    public function index()
    {
        $seller = Seller::where('user_id', auth()->id())->firstOrFail();

        $dispatches = $seller->dispatchOrders()
            ->with('items.product')
            ->latest()
            ->paginate(20);

        return view('seller-dispatches.index', compact('dispatches','seller'));
    }
}
