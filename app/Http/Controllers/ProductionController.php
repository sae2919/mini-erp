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
            ->paginate(10)
            ->withQueryString();

        $totalCost  = Production::sum('total_cost');
        $thisMonth  = Production::where('production_date', '>=', now()->startOfMonth())
                                ->sum('total_cost');
        $totalUnits = ProductionItem::sum('quantity');

        return view('productions.index', compact('productions', 'totalCost', 'thisMonth', 'totalUnits'));
    }

    // ─────────────────────────────────────────────────────────────
    // CREATE
    // ─────────────────────────────────────────────────────────────
    public function create()
{
    $products = Product::active()
        ->with('category')
        ->orderBy('name')
        ->get();

    // Falls back to cost_price when production_cost is not set
    $productsJs = $products->map(fn($p) => [
        'id'   => $p->id,
        'name' => $p->name . ' (' . $p->sku . ')',
        'cost' => (float) ($p->production_cost > 0 ? $p->production_cost : $p->cost_price),
    ]);

    $query = DB::table('products')
    ->join('categories', 'products.category_id', '=', 'categories.id')
    ->select(
        'products.id',
        'products.name as product_name',
        'products.sku as product_sku',
        'categories.name as category_name',
        'products.stock_quantity as warehouse',
        DB::raw('(SELECT COALESCE(SUM(pi.quantity),0) FROM production_items pi WHERE pi.product_id = products.id) as produced'),
        DB::raw('(SELECT COALESCE(SUM(di.quantity),0) FROM dispatch_items di WHERE di.product_id = products.id) as dispatched'),
        DB::raw('(SELECT COALESCE(SUM(si.quantity),0) FROM sale_items si WHERE si.product_id = products.id) as sold'),
        DB::raw('(GREATEST(0,
            (SELECT COALESCE(SUM(di.quantity),0) FROM dispatch_items di WHERE di.product_id = products.id)
          - (SELECT COALESCE(SUM(ss.quantity),0) FROM seller_sale_items ss WHERE ss.product_id = products.id)
        )) as with_sellers'),
        DB::raw('(products.stock_quantity +
            GREATEST(0,
                (SELECT COALESCE(SUM(di.quantity),0) FROM dispatch_items di WHERE di.product_id = products.id)
              - (SELECT COALESCE(SUM(ss.quantity),0) FROM seller_sale_items ss WHERE ss.product_id = products.id)
            )
        ) as total_stock')
    )
    ->where('products.is_active', true)
    ->orderBy('categories.name')
    ->orderBy('products.name');


// 🔥 GET FULL DATA (for totals)
$allData = $query->get();

$totals = [
    'produced' => $allData->sum('produced'),
    'dispatched' => $allData->sum('dispatched'),
    'sold' => $allData->sum('sold'),
    'warehouse' => $allData->sum('warehouse'),
    'with_sellers' => $allData->sum('with_sellers'),
    'total_stock' => $allData->sum('total_stock'),
];


// 🔥 PAGINATION (10 per page)
$page = request()->page ?? 1;

$report = new \Illuminate\Pagination\LengthAwarePaginator(
    $allData->forPage($page, 10),
    $allData->count(),
    10,
    $page,
    [
        'path' => request()->url(),
        'query' => request()->query()
    ]
);

    return view('productions.create', compact('products', 'productsJs', 'report', 'totals'));
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
    public function exportStockReport(Request $request)
{
    $data = DB::table('products')
        ->join('categories', 'products.category_id', '=', 'categories.id')
        ->select(
            'products.name as product',
            'categories.name as category',
            'products.stock_quantity as warehouse',
            DB::raw('(SELECT COALESCE(SUM(pi.quantity),0) FROM production_items pi WHERE pi.product_id = products.id) as produced'),
            DB::raw('(SELECT COALESCE(SUM(di.quantity),0) FROM dispatch_items di WHERE di.product_id = products.id) as dispatched'),
            DB::raw('(SELECT COALESCE(SUM(si.quantity),0) FROM sale_items si WHERE si.product_id = products.id) as sold'),
            DB::raw('(products.stock_quantity) as total_stock')
        )
        ->get();

    $filename = "stock_report.xlsx";

    $headers = [
        "Content-Type" => "text/csv",
        "Content-Disposition" => "attachment; filename=$filename",
    ];

    $callback = function () use ($data) {
        $file = fopen('php://output', 'w');

        fputcsv($file, [
            'Product',
            'Category',
            'Produced',
            'Dispatched',
            'Sold',
            'Warehouse',
            'Total Stock'
        ]);

        foreach ($data as $row) {
            fputcsv($file, [
                $row->product,
                $row->category,
                $row->produced,
                $row->dispatched,
                $row->sold,
                $row->warehouse,
                $row->total_stock
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}
}