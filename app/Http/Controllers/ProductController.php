<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Services\ActivityLogger;
use App\Services\InventoryService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX — paginated product list with search + category filter
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
{
    $products = Product::with('category')
        ->when($request->search, fn($q) => $q->search($request->search))
        ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
        ->when($request->stock_status === 'low', fn($q) => $q->lowStock())
        ->when($request->stock_status === 'out', fn($q) => $q->where('stock_quantity', 0))
        ->orderBy('name')
        ->paginate(10)
        ->withQueryString();

    $categories    = Category::orderBy('name')->get();
    $showCostPrice = ! auth()->user()->hasAnyRole(['sales_executive', 'viewer']); // ← was missing

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
        ActivityLogger::log('created', $product, "Created product: {$product->name}");
        return redirect()->route('products.index')->with('success', 'Product created.');
    }

    public function show(Product $product)
    {
        $product->load(['category', 'stockMovements' => fn($q) => $q->latest()->limit(20)]);
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $canEditCostPrice = auth()->user()->hasRole('admin');
        return view('products.edit', compact('product', 'categories', 'canEditCostPrice'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        ActivityLogger::log('updated', $product, "Updated product: {$product->name}");
        return redirect()->route('products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        ActivityLogger::log('deleted', $product, "Deleted product: {$product->name}");
        return redirect()->route('products.index')->with('success', 'Product deleted.');
    }
}
