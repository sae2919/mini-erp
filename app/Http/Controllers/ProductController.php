<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->search, fn($q) => $q->search($request->search))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->stock_status === 'low', fn($q) => $q->lowStock())
            ->when($request->stock_status === 'out', fn($q) => $q->where('stock_quantity', 0))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        $showCostPrice = !auth()->user()->hasRole('sales_executive') &&
                         !auth()->user()->hasRole('viewer');

        return view('products.index', compact('products', 'categories', 'showCostPrice'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        $product = Product::create($request->validated() + ['stock_quantity' => 0]);

        ActivityLogger::created($product, "Product \"{$product->name}\" created");

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'purchaseItems.purchase.supplier',
            'saleItems.sale',
        ]);

        $showCostPrice = !auth()->user()->hasRole('sales_executive') &&
                         !auth()->user()->hasRole('viewer');

        return view('products.show', compact('product', 'showCostPrice'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        $canEditPricing = auth()->user()->hasRole('admin');

        return view('products.edit', compact('product', 'categories', 'canEditPricing'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required',
            'sku' => 'required',
            'category_id' => 'required',

            'price' => 'nullable|numeric',
            'stock_quantity' => 'nullable|integer',
            'add_stock' => 'nullable|integer|min:0',
        ]);

        // ✅ Update normal fields (NO stock overwrite)
        $product->update([
    'name' => $request->name,
    'category_id' => $request->category_id,
    'unit' => $request->unit,
    'price' => $request->price ?? $product->price, // ✅ fix
]);

        // ✅ Add stock ONLY if provided
        if ($request->filled('add_stock') && $request->add_stock > 0) {

            // Increase stock
            // Increase stock
$product->increment('stock_quantity', $request->add_stock);

// ✅ Direct stock movement log (FIX)
\App\Models\StockMovement::create([
    'product_id' => $product->id,
    'type' => 'in',
    'quantity' => $request->add_stock,
    'reference_type' => 'manual',
    'reference_id' => null,
    'note' => 'Manual stock added',
    'user_id' => auth()->id(),
]);
            // 🔥 Activity log (optional but good)
            ActivityLogger::updated($product, "Stock increased by {$request->add_stock}");
        }

        return redirect()->route('products.index')
            ->with('success', 'Stock updated!');
    }

    public function destroy(Product $product)
    {
        if ($product->saleItems()->exists() || $product->purchaseItems()->exists()) {
            return back()->with('error',
                'Cannot delete product with existing purchase or sale records.');
        }

        ActivityLogger::deleted($product, "Product \"{$product->name}\" deleted");

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted.');
    }
    public function stockHistory(Product $product)
{
    $logs = \App\Models\StockMovement::where('product_id', $product->id)
                ->latest()
                ->get();

    return view('products.stock-history', compact('product', 'logs'));
}
}