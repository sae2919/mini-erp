<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 20, 50, 100]) ? $perPage : 10;

        $categories = Category::withCount('products')
            ->when($request->filled('search'), fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            )
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category = Category::create($request->only('name', 'description'));
        ActivityLogger::log('created', $category, "Created category: {$category->name}");

        return redirect()->route('categories.index')->with('success', 'Category created.');
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:100', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($request->only('name', 'description'));
        ActivityLogger::log('updated', $category, "Updated category: {$category->name}");

        return redirect()->route('categories.index')->with('success', 'Category updated.');
    }

    public function destroy(Category $category)
    {
        if ($category->products()->count()) {
            return back()->with('error', 'Cannot delete — category has products assigned.');
        }

        $name = $category->name;
        $category->delete();
        ActivityLogger::log('deleted', $category, "Deleted category: {$name}");

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
