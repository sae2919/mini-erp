<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockAdjustment;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StockAdjustmentController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $adjustments = StockAdjustment::with(['product', 'user'])
            ->dateRange($request->from, $request->to)
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $products = Product::active()->orderBy('name')->get();

        return view('stock_adjustments.index', compact('adjustments', 'products'));
    }

    public function create(Request $request)
    {
        $products = Product::active()->with('category')->orderBy('name')->get();

        // Allow pre-selecting a product via query string e.g. /stock-adjustments/create?product_id=5
        $selectedProduct = $request->product_id
            ? $products->firstWhere('id', $request->product_id)
            : null;

        return view('stock_adjustments.create', compact('products', 'selectedProduct'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'type'       => ['required', 'in:add,subtract'],
            'quantity'   => ['required', 'integer', 'min:1', 'max:99999'],
            'reason'     => ['required', 'string', 'max:255'],
            'notes'      => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->inventoryService->adjustStock(
                productId: (int) $validated['product_id'],
                type:      $validated['type'],
                quantity:  (int) $validated['quantity'],
                reason:    $validated['reason'],
                notes:     $validated['notes'] ?? null,
                userId:    auth()->id(),
            );

            return redirect()->route('stock-adjustments.index')
                ->with('success', 'Stock adjustment recorded successfully.');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }
}
