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
    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────
    public function index()
    {
        $productions = Production::with(['user', 'items.product'])
            ->latest()
            ->paginate(20);

        $totalCost  = Production::sum('total_cost');
        $thisMonth  = Production::where('production_date', '>=', now()->startOfMonth())
            ->sum('total_cost');
        $totalUnits = ProductionItem::sum('quantity');

        return view('productions.index', compact('productions', 'totalCost', 'thisMonth', 'totalUnits'));
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE — form + stock movement report side by side
    // ─────────────────────────────────────────────────────────────
    public function create()
    {
        // Products for the form dropdown
        $products = Product::active()
            ->with('category')
            ->orderBy('name')
            ->get();

        // Pre-mapped for JS — avoids ALL Blade @php/@json parser issues
        $productsJs = $products->map(fn($p) => [
            'id'   => $p->id,
            'name' => $p->name . ' (' . $p->sku . ')',
            'cost' => (float) $p->production_cost,
        ]);

        // Stock movement report — correlated subqueries (no cartesian product)
        $report = DB::table('products')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'products.id',
                'products.name           as product_name',
                'products.sku            as product_sku',
                'categories.name         as category_name',
                'products.stock_quantity as warehouse',

                // Total units produced
                DB::raw('(
                    SELECT COALESCE(SUM(pi.quantity), 0)
                    FROM production_items pi
                    WHERE pi.product_id = products.id
                ) as produced'),

                // Total units dispatched to sellers
                DB::raw('(
                    SELECT COALESCE(SUM(di.quantity), 0)
                    FROM dispatch_items di
                    WHERE di.product_id = products.id
                ) as dispatched'),

                // Total units sold (direct / POS sales)
                DB::raw('(
                    SELECT COALESCE(SUM(si.quantity), 0)
                    FROM sale_items si
                    WHERE si.product_id = products.id
                ) as sold'),

                // With sellers = dispatched minus what sellers have sold
                // Uses seller_sale_items NOT sale_items to avoid negative values
                DB::raw('(
    GREATEST(0,
        (SELECT COALESCE(SUM(di.quantity), 0) FROM dispatch_items di WHERE di.product_id = products.id)
      - (SELECT COALESCE(SUM(ss.quantity), 0) FROM seller_sale_items ss WHERE ss.product_id = products.id)
    )
) as with_sellers'),

DB::raw('(
    products.stock_quantity
  + GREATEST(0,
        (SELECT COALESCE(SUM(di.quantity), 0) FROM dispatch_items di WHERE di.product_id = products.id)
      - (SELECT COALESCE(SUM(ss.quantity), 0) FROM seller_sale_items ss WHERE ss.product_id = products.id)
    )
) as total_stock')

            ) // <-- closing ->select()
            ->where('products.is_active', true)
            ->orderBy('categories.name')
            ->orderBy('products.name')
            ->get();

        return view('productions.create', compact('products', 'productsJs', 'report'));
    }

    // ─────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'production_date'    => ['required', 'date', 'before_or_equal:today'],
            'notes'              => ['nullable', 'string', 'max:1000'],
            'items'              => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'  => ['required', 'numeric', 'min:0'],
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

                // Atomic increment — safe against race conditions
                Product::where('id', $item['product_id'])
                    ->increment('stock_quantity', $item['quantity']);

                $totalCost  += $subtotal;
                $totalUnits += $item['quantity'];
            }

            $production->update(['total_cost' => $totalCost]);

            ActivityLogger::created(
                $production,
                "Production batch {$production->reference} — {$totalUnits} units, ₹{$totalCost}"
            );
        });

        return redirect()
            ->route('productions.index')
            ->with('success', 'Production batch recorded. Warehouse stock updated.');
    }

    // ─────────────────────────────────────────────────────────────
    // SHOW
    // ─────────────────────────────────────────────────────────────
    public function show(Production $production)
    {
        $production->load(['items.product.category', 'user']);

        return view('productions.show', compact('production'));
    }

    // ─────────────────────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────────────────────
    public function destroy(Production $production)
    {
        DB::transaction(function () use ($production) {
            foreach ($production->items as $item) {
                Product::where('id', $item->product_id)
                    ->decrement('stock_quantity', $item->quantity);
            }

            ActivityLogger::deleted(
                $production,
                "Production batch {$production->reference} deleted. Stock reversed."
            );

            $production->delete();
        });

        return redirect()
            ->route('productions.index')
            ->with('success', 'Production batch deleted. Stock reversed.');
    }
}