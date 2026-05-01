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
            ->when($request->search,      fn($q) => $q->search($request->search))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->stock_status === 'low', fn($q) => $q->lowStock())
            ->when($request->stock_status === 'out', fn($q) => $q->where('stock_quantity', 0))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('name')->get();

        // Sales executives cannot see cost prices
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

        // Inventory managers cannot edit price or cost_price
        $canEditPricing = auth()->user()->hasRole('admin');

        return view('products.edit', compact('product', 'categories', 'canEditPricing'));
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        $data = collect($request->validated())->except('stock_quantity')->toArray();

        // Inventory managers cannot change pricing fields
        if (!auth()->user()->hasRole('admin')) {
            unset($data['price'], $data['cost_price']);
        }

        $product->update($data);
        ActivityLogger::updated($product, "Product \"{$product->name}\" updated");

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
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
}
