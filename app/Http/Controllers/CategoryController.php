<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::withCount('products')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('categories.index', compact('categories'));
    }

    public function create()
    {
        return view('categories.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', "Category \"{$validated['name']}\" created.");
    }

    public function edit(Category $category)
    {
        return view('categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:categories,name,' . $category->id],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', "Category updated.");
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error',
                "Cannot delete \"{$category->name}\" — it has {$category->products()->count()} product(s) assigned to it.");
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', "Category deleted.");
    }
}
