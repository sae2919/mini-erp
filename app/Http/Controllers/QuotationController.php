<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        // Auto-expire passed valid_until
        Quotation::where('status', 'sent')
            ->where('valid_until', '<', now())
            ->update(['status' => 'expired']);

        $quotations = Quotation::with('customer')
            ->dateRange($request->from, $request->to)
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderByDesc('quotation_date')
            ->paginate(20)
            ->withQueryString();

        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        $customers = Customer::active()->orderBy('name')->get();
        $products  = Product::active()->with('category')->orderBy('name')->get();
        return view('quotations.create', compact('customers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_name'         => ['required', 'string', 'max:255'],
            'quotation_date'        => ['required', 'date'],
            'valid_until'           => ['required', 'date', 'after:quotation_date'],
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'exists:products,id'],
            'items.*.quantity'      => ['required', 'integer', 'min:1'],
            'items.*.unit_price'    => ['required', 'numeric', 'min:0'],
            'items.*.discount'      => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        DB::transaction(function () use ($request) {
            $subtotal = 0;
            $taxTotal = 0;

            $quotation = Quotation::create([
                'reference'      => Quotation::generateReference(),
                'customer_id'    => $request->customer_id ?? null,
                'customer_name'  => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'quotation_date' => $request->quotation_date,
                'valid_until'    => $request->valid_until,
                'status'         => $request->action === 'send' ? 'sent' : 'draft',
                'notes'          => $request->notes,
                'terms'          => $request->terms,
                'subtotal'       => 0,
                'tax_amount'     => 0,
                'total_amount'   => 0,
            ]);

            foreach ($request->items as $item) {
                $product  = Product::find($item['product_id']);
                $discount = $item['discount'] ?? 0;
                $taxRate  = $product->tax?->rate ?? 0;
                $lineAmt  = $item['quantity'] * $item['unit_price'] * (1 - $discount / 100);
                $taxAmt   = $lineAmt * ($taxRate / 100);

                QuotationItem::create([
                    'quotation_id' => $quotation->id,
                    'product_id'   => $item['product_id'],
                    'quantity'     => $item['quantity'],
                    'unit_price'   => $item['unit_price'],
                    'tax_rate'     => $taxRate,
                    'discount'     => $discount,
                    'subtotal'     => $lineAmt + $taxAmt,
                ]);

                $subtotal += $lineAmt;
                $taxTotal += $taxAmt;
            }

            $quotation->update([
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxTotal,
                'total_amount' => $subtotal + $taxTotal,
            ]);

            ActivityLogger::created($quotation, "Quotation {$quotation->reference} created");
        });

        return redirect()->route('quotations.index')->with('success', 'Quotation created.');
    }

    public function show(Quotation $quotation)
    {
        $quotation->load('items.product', 'customer', 'convertedSale');
        return view('quotations.show', compact('quotation'));
    }

    public function updateStatus(Request $request, Quotation $quotation)
    {
        $request->validate(['status' => 'required|in:draft,sent,accepted,rejected']);
        $quotation->update(['status' => $request->status]);
        return back()->with('success', 'Quotation status updated.');
    }

    public function convertToSale(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Already converted to a sale.');
        }

        $sale = DB::transaction(function () use ($quotation) {
            $quotation->load('items.product');

            $sale = Sale::create([
                'reference'      => Sale::generateReference('offline'),
                'customer_id'    => $quotation->customer_id,
                'customer_name'  => $quotation->customer_name,
                'sale_date'      => now()->toDateString(),
                'total_amount'   => $quotation->total_amount,
                'tax_amount'     => $quotation->tax_amount,
                'subtotal_amount'=> $quotation->subtotal,
                'notes'          => "Converted from quotation {$quotation->reference}",
                'order_type'     => 'offline',
                'status'         => 'completed',
                'payment_status' => 'unpaid',
            ]);

            foreach ($quotation->items as $item) {
                $product = Product::where('id', $item->product_id)->lockForUpdate()->first();

                SaleItem::create([
                    'sale_id'       => $sale->id,
                    'product_id'    => $product->id,
                    'quantity'      => $item->quantity,
                    'selling_price' => $item->unit_price,
                    'cost_price'    => $product->cost_price,
                    'tax_rate'      => $item->tax_rate,
                    'tax_amount'    => $item->subtotal - ($item->quantity * $item->unit_price * (1 - $item->discount / 100)),
                ]);

                $product->decrement('stock_quantity', $item->quantity);
            }

            $quotation->update([
                'status'               => 'converted',
                'converted_to_sale_id' => $sale->id,
            ]);

            ActivityLogger::log('converted', 'Quotation',
                "Quotation {$quotation->reference} converted to sale {$sale->reference}",
                $quotation->id
            );

            return $sale;
        });

        return redirect()->route('sales.show', $sale)
            ->with('success', "Quotation converted to sale {$sale->reference}.");
    }

    public function destroy(Quotation $quotation)
    {
        if ($quotation->status === 'converted') {
            return back()->with('error', 'Cannot delete a converted quotation.');
        }
        $quotation->delete();
        return redirect()->route('quotations.index')->with('success', 'Quotation deleted.');
    }
}
