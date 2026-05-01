<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;

class StorefrontController extends Controller
{
    public function index()
    {
        $search     = request('search');
        $categoryId = request('category');

        $products = Product::where('is_active', true)
    ->where('stock_quantity', '>', 0)
    ->with('category')
    ->when($search, fn($q) => $q->where(function($q2) use ($search) {
        $q2->where('name', 'like', "%{$search}%")
           ->orWhere('sku', 'like', "%{$search}%");
    }))
    ->when($categoryId, fn($q) => $q->where('category_id', $categoryId))
    ->orderBy('name')   // ← removed orderByDesc('is_featured')
    ->paginate(12)
    ->withQueryString();

$categories = Category::whereHas('products', fn($q) =>
    $q->where('is_active', true)->where('stock_quantity', '>', 0)
)->orderBy('name')->get();

$featured = collect(); // ← empty collection, no is_featured check
    }

    public function show(Product $product)
    {
        abort_if(!$product->is_active, 404);

        $related = Product::where('is_active', true)
            ->where('stock_quantity', '>', 0)
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->take(4)->get();

        return view('storefront.show', compact('product', 'related'));
    }
}
