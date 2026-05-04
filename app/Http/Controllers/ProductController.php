<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Services\ActivityLogger;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class ProductController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

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

        $showCostPrice = ! auth()->user()->hasAnyRole(['sales_executive', 'viewer']);

        return view('products.index', compact('products', 'categories', 'showCostPrice'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request)
    {
        try {

            DB::transaction(function () use ($request, &$product) {

                $product = Product::create(
                    $request->validated() + ['stock_quantity' => 0]
                );

                ActivityLogger::created(
                    $product,
                    "Product \"{$product->name}\" created (SKU: {$product->sku})"
                );
            });

            return redirect()->route('products.index')
                ->with('success', 'Product created successfully.');

        } catch (QueryException $e) {

            return back()
                ->withInput()
                ->with('error', 'SKU already exists. Please use a unique SKU.');
        }
    }

    public function show(Product $product)
    {
        $product->load([
            'category',
            'purchaseItems.purchase.supplier',
            'saleItems.sale',
        ]);

        $showCostPrice = ! auth()->user()->hasAnyRole(['sales_executive', 'viewer']);

        return view('products.show', compact('product', 'showCostPrice'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $canEditCostPrice = auth()->user()->hasRole('admin');

        return view('products.edit', compact('product', 'categories', 'canEditCostPrice'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $validated = $request->validated();

        // ✅ Stock via service (safe + logged)
        if (! empty($validated['add_stock']) && $validated['add_stock'] > 0) {

            $this->inventoryService->adjustStock(
                productId: $product->id,
                type: 'add',
                quantity: (int) $validated['add_stock'],
                reason: 'manual_adjustment',
                notes: $validated['stock_notes'] ?? 'Manual stock addition via product edit',
                userId: auth()->id(),
            );
        }

        // Remove stock-only fields
        unset($validated['add_stock'], $validated['stock_notes']);

        // 🔐 Only admin can change cost_price
        if (! auth()->user()->hasRole('admin')) {
            unset($validated['cost_price']);
        }

        // ❌ Never allow direct stock overwrite
        unset($validated['stock_quantity']);

        $product->update($validated);

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

        ActivityLogger::deleted($product, "Product \"{$product->name}\" (SKU: {$product->sku}) deleted");

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted.');
    }

    public function stockHistory(Product $product)
    {
        $logs = StockMovement::where('product_id', $product->id)
            ->with('user')
            ->latest()
            ->paginate(30);

        return view('products.stock-history', compact('product', 'logs'));
    }
}