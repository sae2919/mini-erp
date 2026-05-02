<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\ErpNotification;
use App\Models\Seller;
use App\Models\SellerSale;
use App\Models\SellerSaleItem;
use App\Models\SellerStock;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SellerSaleController extends Controller
{
    private function mySellerOrNull(): ?Seller
    {
        return auth()->user()->hasRole('seller')
            ? Seller::where('user_id', auth()->id())->firstOrFail()
            : null;
    }

    public function index(Request $request)
    {
        $mySeller = $this->mySellerOrNull();
        $sellers  = $mySeller ? collect() : Seller::active()->orderBy('name')->get();

        $sales = SellerSale::with(['seller','items.product'])
            ->when($mySeller, fn($q)=>$q->where('seller_id',$mySeller->id))
            ->when(!$mySeller && $request->seller_id, fn($q)=>$q->where('seller_id',$request->seller_id))
            ->when($request->from, fn($q)=>$q->whereDate('sale_date','>=',$request->from))
            ->when($request->to, fn($q)=>$q->whereDate('sale_date','<=',$request->to))
            ->latest()->paginate(20)->withQueryString();

        return view('seller-sales.index', compact('sales','sellers','mySeller'));
    }

    public function create(Request $request)
    {
        $mySeller = $this->mySellerOrNull();

        if ($mySeller) {
            $stock = $mySeller->stocks()->with('product.category')->where('quantity','>',0)->get();
            return view('seller-sales.create', compact('mySeller','stock'));
        }

        $sellers        = Seller::active()->orderBy('name')->get();
        $selectedSeller = $request->seller_id ? Seller::find($request->seller_id) : null;
        $stock          = $selectedSeller
            ? $selectedSeller->stocks()->with('product.category')->where('quantity','>',0)->get()
            : collect();

        return view('seller-sales.create', compact('sellers','selectedSeller','stock'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'seller_id'             => ['required','exists:sellers,id'],
            'customer_name'         => ['nullable','string','max:255'],
            'customer_phone'        => ['nullable','string','max:20'],
            'sale_date'             => ['required','date'],
            'items'                 => ['required','array','min:1'],
            'items.*.product_id'    => ['required','exists:products,id'],
            'items.*.quantity'      => ['required','integer','min:1'],
            'items.*.selling_price' => ['required','numeric','min:0'],
        ]);

        if (auth()->user()->hasRole('seller')) {
            $me = $this->mySellerOrNull();
            abort_if($me->id != $request->seller_id, 403);
        }

        try {
            $sale = DB::transaction(function () use ($request) {
                $seller          = Seller::find($request->seller_id);
                $totalAmount     = 0;
                $totalCommission = 0;
                $totalCompany    = 0;

                $sale = SellerSale::create([
                    'seller_id'         => $seller->id,
                    'reference'         => SellerSale::generateReference($seller->id),
                    'customer_name'     => $request->customer_name,
                    'customer_phone'    => $request->customer_phone,
                    'sale_date'         => $request->sale_date,
                    'total_amount'      => 0,
                    'commission_amount' => 0,
                    'company_amount'    => 0,
                ]);

                foreach ($request->items as $item) {
                    $ss = SellerStock::where('seller_id',$seller->id)
                        ->where('product_id',$item['product_id'])
                        ->lockForUpdate()->first();

                    if (!$ss || $ss->quantity < $item['quantity']) {
                        throw ValidationException::withMessages([
                            'items' => 'Insufficient seller stock. Available: '.($ss?->quantity ?? 0)
                        ]);
                    }

                    $product        = $ss->product;
                    $qty            = (int)$item['quantity'];
                    $sellingPrice   = (float)$item['selling_price'];
                    $dispatchPrice  = (float)$product->dispatch_price;
                    $commissionRate = (float)$product->commission_rate;

                    $commAmt  = round($qty * $dispatchPrice * ($commissionRate / 100), 2);
                    $subtotal = $qty * $sellingPrice;

                    SellerSaleItem::create([
                        'seller_sale_id'    => $sale->id,
                        'product_id'        => $product->id,
                        'quantity'          => $qty,
                        'selling_price'     => $sellingPrice,
                        'dispatch_price'    => $dispatchPrice,
                        'commission_rate'   => $commissionRate,
                        'commission_amount' => $commAmt,
                        'subtotal'          => $subtotal,
                    ]);

                    $ss->decrement('quantity', $qty);

                    $totalAmount     += $subtotal;
                    $totalCommission += $commAmt;
                    $totalCompany    += $qty * $dispatchPrice;
                }

                $sale->update([
                    'total_amount'      => $totalAmount,
                    'commission_amount' => $totalCommission,
                    'company_amount'    => $totalCompany,
                ]);

                Commission::create([
                    'seller_id'      => $seller->id,
                    'seller_sale_id' => $sale->id,
                    'amount'         => $totalCommission,
                    'status'         => 'pending',
                ]);

                ActivityLogger::created($sale,
                    "Seller sale {$sale->reference} by {$seller->name} — ₹{$totalAmount} | Commission: ₹{$totalCommission}"
                );

                ErpNotification::notify('sale',
                    "New Sale by {$seller->name}",
                    "₹{$totalAmount} sale | Commission: ₹{$totalCommission}",
                    ['icon'=>'💰','color'=>'green']
                );

                return $sale;
            });

            return redirect()->route('seller-sales.show',$sale)
                ->with('success',"Sale {$sale->reference} recorded.");

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(SellerSale $sellerSale)
    {
        if (auth()->user()->hasRole('seller')) {
            abort_if($sellerSale->seller_id !== $this->mySellerOrNull()?->id, 403);
        }

        $sellerSale->load(['seller','items.product','commission']);
        return view('seller-sales.show', compact('sellerSale'));
    }
}