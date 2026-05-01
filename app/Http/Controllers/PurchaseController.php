<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PurchaseController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $purchases = Purchase::with(['supplier', 'items'])
            ->dateRange($request->from, $request->to)
            ->when($request->supplier_id, fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->orderByDesc('purchase_date')
            ->paginate(20)
            ->withQueryString();

        $suppliers = Supplier::active()->orderBy('name')->get();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get();
        $products  = Product::active()->with('category')->orderBy('name')->get();

        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseRequest $request)
    {
        try {
            $purchase = $this->inventoryService->createPurchase($request->validated());
            return redirect()->route('purchases.show', $purchase)
                ->with('success', "Purchase {$purchase->reference} recorded. Stock updated.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'items.product.category']);
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        try {
            $this->inventoryService->deletePurchase($purchase);
            return redirect()->route('purchases.index')
                ->with('success', "Purchase {$purchase->reference} reversed. Stock updated.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
