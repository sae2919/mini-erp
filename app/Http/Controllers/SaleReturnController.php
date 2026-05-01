<?php

namespace App\Http\Controllers;

use App\Models\ErpNotification;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleReturn;
use App\Models\SaleReturnItem;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function index()
    {
        $returns = SaleReturn::with(['sale', 'user'])
            ->latest()
            ->paginate(20);

        return view('returns.index', compact('returns'));
    }

    public function create(Sale $sale)
    {
        $sale->load('items.product');
        return view('returns.create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'reason'               => ['required', 'string'],
            'notes'                => ['nullable', 'string', 'max:1000'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.sale_item_id' => ['required'],
            'items.*.quantity'     => ['required', 'integer', 'min:0'],
        ]);

        // Filter out zero-quantity items
        $returnItems = collect($request->items)->filter(fn($i) => $i['quantity'] > 0);

        if ($returnItems->isEmpty()) {
            return back()->withErrors(['items' => 'Select at least one item to return.']);
        }

        DB::transaction(function () use ($request, $sale, $returnItems) {
            $totalAmount = 0;

            $saleReturn = SaleReturn::create([
                'sale_id'      => $sale->id,
                'user_id'      => auth()->id(),
                'reference'    => SaleReturn::generateReference(),
                'return_date'  => now()->toDateString(),
                'reason'       => $request->reason,
                'status'       => 'approved',
                'notes'        => $request->notes,
                'total_amount' => 0,
            ]);

            foreach ($returnItems as $item) {
                $saleItem = $sale->items()->find($item['sale_item_id']);
                if (!$saleItem) continue;

                $qty      = min((int)$item['quantity'], $saleItem->quantity);
                $subtotal = $qty * $saleItem->selling_price;

                SaleReturnItem::create([
                    'sale_return_id' => $saleReturn->id,
                    'product_id'     => $saleItem->product_id,
                    'quantity'       => $qty,
                    'price'          => $saleItem->selling_price,
                    'subtotal'       => $subtotal,
                ]);

                // Return stock
                Product::where('id', $saleItem->product_id)
                    ->lockForUpdate()->first()
                    ->increment('stock_quantity', $qty);

                $totalAmount += $subtotal;
            }

            $saleReturn->update(['total_amount' => $totalAmount]);

            ErpNotification::notify('return',
                "Return Processed — {$saleReturn->reference}",
                "Return of ₹{$totalAmount} processed for sale {$sale->reference}",
                ['icon' => '↩️', 'color' => 'orange', 'url' => route('sales.show', $sale)]
            );

            ActivityLogger::created($saleReturn,
                "Return {$saleReturn->reference} processed for sale {$sale->reference}"
            );
        });

        return redirect()->route('returns.index')
            ->with('success', 'Return processed. Stock has been restored.');
    }

    public function show(SaleReturn $return)
    {
        $return->load(['sale', 'items.product', 'user']);
        return view('returns.show', compact('return'));
    }
}
