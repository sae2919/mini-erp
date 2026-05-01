<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $returns = PurchaseReturn::with(['purchase', 'supplier', 'user'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->from,   fn($q) => $q->whereDate('return_date', '>=', $request->from))
            ->when($request->to,     fn($q) => $q->whereDate('return_date', '<=', $request->to))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $totalReturned   = PurchaseReturn::where('status', 'approved')->sum('total_amount');
        $thisMonthReturn = PurchaseReturn::where('status', 'approved')
            ->where('return_date', '>=', now()->startOfMonth())
            ->sum('total_amount');
        $totalCount      = PurchaseReturn::count();

        return view('purchase-returns.index', compact(
            'returns', 'totalReturned', 'thisMonthReturn', 'totalCount'
        ));
    }

    public function create(Purchase $purchase)
    {
        $purchase->load(['items.product', 'supplier']);

        // Show only items that still have returnable stock
        $returnedQtys = PurchaseReturnItem::whereHas('purchaseReturn', fn($q) =>
            $q->where('purchase_id', $purchase->id)->where('status', 'approved')
        )->selectRaw('product_id, SUM(quantity) as returned')
         ->groupBy('product_id')
         ->pluck('returned', 'product_id');

        return view('purchase-returns.create', compact('purchase', 'returnedQtys'));
    }

    public function store(Request $request, Purchase $purchase)
    {
        $request->validate([
            'reason'               => ['required', 'string'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:0'],
            'items.*.price'        => ['required', 'numeric', 'min:0'],
        ]);

        // Filter zero qty
        $returnItems = collect($request->items)->filter(fn($i) => (int)$i['quantity'] > 0);

        if ($returnItems->isEmpty()) {
            return back()->withErrors(['items' => 'Enter at least one item quantity to return.']);
        }

        DB::transaction(function () use ($request, $purchase, $returnItems) {
            $totalAmount = 0;

            $purchaseReturn = PurchaseReturn::create([
                'purchase_id'  => $purchase->id,
                'supplier_id'  => $purchase->supplier_id,
                'user_id'      => auth()->id(),
                'reference'    => PurchaseReturn::generateReference(),
                'return_date'  => now()->toDateString(),
                'reason'       => $request->reason,
                'status'       => 'approved',
                'notes'        => $request->notes,
                'total_amount' => 0,
            ]);

            foreach ($returnItems as $item) {
                $product  = Product::where('id', $item['product_id'])->lockForUpdate()->first();
                $qty      = (int) $item['quantity'];
                $price    = (float) $item['price'];
                $subtotal = $qty * $price;

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id'         => $product->id,
                    'quantity'           => $qty,
                    'price'              => $price,
                    'subtotal'           => $subtotal,
                ]);

                // Deduct from stock (returning to supplier = stock goes down)
                $product->decrement('stock_quantity', $qty);

                $totalAmount += $subtotal;
            }

            $purchaseReturn->update(['total_amount' => $totalAmount]);

            ErpNotification::notify(
                'return',
                "Purchase Return — {$purchaseReturn->reference}",
                "₹{$totalAmount} worth of goods returned to supplier for purchase {$purchase->reference}",
                ['icon' => '↩️', 'color' => 'orange', 'url' => route('purchase-returns.show', $purchaseReturn)]
            );

            ActivityLogger::created(
                $purchaseReturn,
                "Purchase return {$purchaseReturn->reference} processed for {$purchase->reference}"
            );
        });

        return redirect()->route('purchase-returns.index')
            ->with('success', 'Purchase return processed. Stock has been adjusted.');
    }

    public function show(PurchaseReturn $purchaseReturn)
    {
        $purchaseReturn->load(['purchase.supplier', 'items.product', 'user']);
        return view('purchase-returns.show', compact('purchaseReturn'));
    }
}
