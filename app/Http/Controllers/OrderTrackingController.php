<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

class OrderTrackingController extends Controller
{
    public function track(string $reference)
    {
        $sale = Sale::with('items.product')
            ->where('reference', $reference)
            ->firstOrFail();

        return view('orders.track', compact('sale'));
    }

    public function search(Request $request)
    {
        if ($request->isMethod('post')) {
            $request->validate(['reference' => 'required|string']);
            return redirect()->route('orders.track', strtoupper($request->reference));
        }

        return view('orders.search');
    }
}
