<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Production;
use App\Models\ProductionItem;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductionController extends Controller
{
    public function index()
    {
        $productions = Production::with(['user','items.product'])->latest()->paginate(20);
        $totalCost   = Production::sum('total_cost');
        $thisMonth   = Production::where('production_date','>=',now()->startOfMonth())->sum('total_cost');
        $totalUnits  = ProductionItem::sum('quantity');
        return view('productions.index', compact('productions','totalCost','thisMonth','totalUnits'));
    }

    public function create()
    {
        $products = Product::active()->with('category')->orderBy('name')->get();
        return view('productions.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'production_date'      => ['required','date'],
            'notes'                => ['nullable','string'],
            'items'                => ['required','array','min:1'],
            'items.*.product_id'   => ['required','exists:products,id'],
            'items.*.quantity'     => ['required','integer','min:1'],
            'items.*.unit_cost'    => ['required','numeric','min:0'],
        ]);

        DB::transaction(function () use ($request) {
            $production = Production::create([
                'user_id'         => auth()->id(),
                'reference'       => Production::generateReference(),
                'production_date' => $request->production_date,
                'notes'           => $request->notes,
                'status'          => 'completed',
                'total_cost'      => 0,
            ]);

            $totalCost  = 0;
            $totalUnits = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['quantity'] * $item['unit_cost'];
                ProductionItem::create([
                    'production_id' => $production->id,
                    'product_id'    => $item['product_id'],
                    'quantity'      => $item['quantity'],
                    'unit_cost'     => $item['unit_cost'],
                    'subtotal'      => $subtotal,
                ]);
                Product::where('id', $item['product_id'])->increment('stock_quantity', $item['quantity']);
                $totalCost  += $subtotal;
                $totalUnits += $item['quantity'];
            }
            $production->update(['total_cost' => $totalCost]);

            ActivityLogger::created($production,
                "Production batch {$production->reference} — {$totalUnits} units, ₹{$totalCost}"
            );
        });

        return redirect()->route('productions.index')
            ->with('success', 'Production batch recorded. Warehouse stock updated.');
    }

    public function show(Production $production)
    {
        $production->load(['items.product.category','user']);
        return view('productions.show', compact('production'));
    }

    public function destroy(Production $production)
    {
        DB::transaction(function () use ($production) {
            foreach ($production->items as $item) {
                Product::where('id',$item->product_id)->decrement('stock_quantity',$item->quantity);
            }
            ActivityLogger::deleted($production,
                "Production batch {$production->reference} deleted. Stock reversed."
            );
            $production->delete();
        });
        return redirect()->route('productions.index')
            ->with('success', 'Production batch deleted. Stock reversed.');
    }
}