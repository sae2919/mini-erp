<?php

namespace App\Http\Controllers;

use App\Models\DispatchItem;
use App\Models\DispatchOrder;
use App\Models\ErpNotification;
use App\Models\Product;
use App\Models\Seller;
use App\Models\SellerPayment;
use App\Models\SellerStock;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DispatchController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX  ← per_page support added; everything else unchanged
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        $dispatches = DispatchOrder::with(['seller', 'items'])
            ->when($request->seller_id,      fn($q) => $q->where('seller_id', $request->seller_id))
            ->when($request->payment_status, fn($q) => $q->where('payment_status', $request->payment_status))
            ->when($request->from,           fn($q) => $q->whereDate('dispatch_date', '>=', $request->from))
            ->when($request->to,             fn($q) => $q->whereDate('dispatch_date', '<=', $request->to))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $sellers      = Seller::active()->orderBy('name')->get();
        $totalValue   = DispatchOrder::where('status', '!=', 'cancelled')->sum('total_amount');
        $totalPending = DispatchOrder::where('payment_status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->sum(DB::raw('total_amount - paid_amount'));

        return view('dispatches.index', compact('dispatches', 'sellers', 'totalValue', 'totalPending'));
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE — UNCHANGED
    // ─────────────────────────────────────────────────────────────
    public function create()
    {
        $sellers  = Seller::active()->orderBy('name')->get();
        $products = Product::active()->where('stock_quantity', '>', 0)->with('category')->orderBy('name')->get();
        return view('dispatches.create', compact('sellers', 'products'));
    }

    // ─────────────────────────────────────────────────────────────
    // STORE — UNCHANGED
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'seller_id'              => ['required', 'exists:sellers,id'],
            'dispatch_date'          => ['required', 'date'],
            'notes'                  => ['nullable', 'string'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.dispatch_price' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $dispatch = DB::transaction(function () use ($request) {
                $dispatch = DispatchOrder::create([
                    'seller_id'      => $request->seller_id,
                    'user_id'        => auth()->id(),
                    'reference'      => DispatchOrder::generateReference(),
                    'dispatch_date'  => $request->dispatch_date,
                    'status'         => 'dispatched',
                    'payment_status' => 'unpaid',
                    'total_amount'   => 0,
                    'notes'          => $request->notes,
                ]);

                $totalAmount = 0;
                foreach ($request->items as $item) {
                    $product = Product::where('id', $item['product_id'])->lockForUpdate()->first();

                    if ($product->stock_quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => "Insufficient stock for \"{$product->name}\". Available: {$product->stock_quantity}"
                        ]);
                    }

                    $subtotal = $item['quantity'] * $item['dispatch_price'];
                    DispatchItem::create([
                        'dispatch_order_id' => $dispatch->id,
                        'product_id'        => $product->id,
                        'quantity'          => $item['quantity'],
                        'dispatch_price'    => $item['dispatch_price'],
                        'subtotal'          => $subtotal,
                    ]);

                    $product->decrement('stock_quantity', $item['quantity']);

                    $ss = SellerStock::firstOrCreate(
                        ['seller_id' => $request->seller_id, 'product_id' => $product->id],
                        ['quantity' => 0]
                    );
                    $ss->increment('quantity', $item['quantity']);
                    $totalAmount += $subtotal;
                }

                $dispatch->update(['total_amount' => $totalAmount]);
                $seller = Seller::find($request->seller_id);
                $seller->increment('balance_due', $totalAmount);

                ActivityLogger::created($dispatch,
                    "Dispatch {$dispatch->reference} → {$seller->name} — ₹{$totalAmount}"
                );

                ErpNotification::notify('dispatch',
                    "Dispatch {$dispatch->reference}",
                    "₹{$totalAmount} dispatched to {$seller->name}",
                    ['icon' => '📦', 'color' => 'blue', 'url' => route('dispatches.show', $dispatch)]
                );

                return $dispatch;
            });

            return redirect()->route('dispatches.show', $dispatch)
                ->with('success', "Dispatch {$dispatch->reference} created successfully.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW — UNCHANGED
    // ─────────────────────────────────────────────────────────────
    public function show(DispatchOrder $dispatch)
    {
        $dispatch->load(['seller', 'user', 'items.product', 'payments']);
        return view('dispatches.show', compact('dispatch'));
    }

    // ─────────────────────────────────────────────────────────────
    // RECORD PAYMENT — UNCHANGED
    // ─────────────────────────────────────────────────────────────
    public function recordPayment(Request $request, DispatchOrder $dispatch)
    {
        $request->validate([
            'amount'    => ['required', 'numeric', 'min:0.01'],
            'method'    => ['required', 'in:cash,upi,bank_transfer,cheque'],
            'reference' => ['nullable', 'string', 'max:255'],
            'paid_at'   => ['required', 'date'],
        ]);

        DB::transaction(function () use ($request, $dispatch) {
            SellerPayment::create([
                'seller_id'         => $dispatch->seller_id,
                'dispatch_order_id' => $dispatch->id,
                'user_id'           => auth()->id(),
                'amount'            => $request->amount,
                'method'            => $request->method,
                'reference'         => $request->reference,
                'paid_at'           => $request->paid_at,
            ]);

            $newPaid = $dispatch->paid_amount + $request->amount;
            $status  = $newPaid >= $dispatch->total_amount ? 'paid' : 'partial';
            $dispatch->update(['paid_amount' => $newPaid, 'payment_status' => $status]);
            $dispatch->seller->decrement('balance_due', $request->amount);

            ActivityLogger::log('payment_received', 'DispatchOrder',
                "Payment ₹{$request->amount} received from {$dispatch->seller->name} for {$dispatch->reference}",
                $dispatch->id
            );
        });

        return back()->with('success', 'Payment recorded.');
    }
}
