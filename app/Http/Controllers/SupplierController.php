<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $suppliers = Supplier::withCount('purchases')
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        Supplier::create($validated);
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier added.');
    }

    public function show(Supplier $supplier)
    {
        $purchases = $supplier->purchases()
            ->with('items.product')
            ->orderByDesc('purchase_date')
            ->paginate(10);

        return view('suppliers.show', compact('supplier', 'purchases'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        $supplier->update($validated);
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier updated.');
    }

    public function destroy(Supplier $supplier)
    {
        if ($supplier->purchases()->exists()) {
            return back()->with('error', 
                'Cannot delete supplier with existing purchase records.');
        }

        $supplier->delete();
        return redirect()->route('suppliers.index')
            ->with('success', 'Supplier deleted.');
    }
}
