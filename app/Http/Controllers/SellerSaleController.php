<?php

namespace App\Http\Controllers;

use App\Models\SellerSale;
use App\Models\Seller;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SellerSaleController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // HELPER — returns the Seller record if logged-in user is a seller
    // ─────────────────────────────────────────────────────────────
    private function mySellerOrNull(): ?Seller
    {
        return auth()->user()->hasRole('seller')
            ? Seller::where('user_id', auth()->id())->firstOrFail()
            : null;
    }

    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $mySeller = $this->mySellerOrNull();

        $sales = SellerSale::with(['seller', 'items.product'])
            ->when($mySeller, fn($q) => $q->where('seller_id', $mySeller->id))
            ->when(!$mySeller && $request->seller_id, fn($q) => $q->where('seller_id', $request->seller_id))
            ->when($request->from, fn($q) => $q->whereDate('sale_date', '>=', $request->from))
            ->when($request->to,   fn($q) => $q->whereDate('sale_date', '<=', $request->to))
            ->latest('sale_date')
            ->paginate(10)
            ->withQueryString();

        $sellers = $mySeller ? collect() : Seller::orderBy('name')->get();

        return view('seller-sales.index', compact('sales', 'sellers', 'mySeller'));
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────
    public function create(Request $request)
{
    $mySeller = $this->mySellerOrNull();

    if ($mySeller) {
        // Seller sees only their own stock
        $stock = $mySeller->stocks()
            ->with('product.category')
            ->where('quantity', '>', 0)
            ->get();
        return view('seller-sales.create', compact('mySeller', 'stock'));
    }

    // Admin/SalesExec can pick any seller
    $sellers        = Seller::active()->orderBy('name')->get();
    $selectedSeller = $request->seller_id ? Seller::find($request->seller_id) : null;
    $stock          = $selectedSeller
        ? $selectedSeller->stocks()->with('product.category')->where('quantity', '>', 0)->get()
        : collect();

    return view('seller-sales.create', compact('sellers', 'selectedSeller', 'stock'));
}

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'seller_id'              => ['required', 'exists:sellers,id'],
            'sale_date'              => ['required', 'date', 'before_or_equal:today'],
            'customer_name'          => ['nullable', 'string', 'max:150'],
            'notes'                  => ['nullable', 'string', 'max:1000'],
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1'],
            'items.*.price_per_unit' => ['required', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $sale = SellerSale::create([
                'seller_id'     => $request->seller_id,
                'sale_date'     => $request->sale_date,
                'customer_name' => $request->customer_name,
                'notes'         => $request->notes,
                'user_id'       => auth()->id(),
                'total_amount'  => 0,
            ]);

            foreach ($request->items as $item) {
                $lineTotal    = $item['quantity'] * $item['price_per_unit'];
                $totalAmount += $lineTotal;

                $sale->items()->create([
                    'product_id'     => $item['product_id'],
                    'quantity'       => $item['quantity'],
                    'price_per_unit' => $item['price_per_unit'],
                    'total_amount'   => $lineTotal,
                ]);

                $seller = Seller::find($request->seller_id);
                if ($seller?->commission_rate) {
                    $commission = $lineTotal * ($seller->commission_rate / 100);
                    $sale->items()->latest()->first()->update(['commission' => $commission]);
                }
            }

            $sale->update(['total_amount' => $totalAmount]);
            ActivityLogger::log('created', $sale, "Seller sale recorded — ₹{$totalAmount}");
        });

        return redirect()->route('seller-sales.index')->with('success', 'Sale recorded successfully.');
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────
    public function show(SellerSale $sellerSale)
    {
        $sellerSale->load(['seller', 'user', 'items.product.category']);
        return view('seller-sales.show', compact('sellerSale'));
    }
}