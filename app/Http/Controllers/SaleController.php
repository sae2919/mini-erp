<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Services\ActivityLogger;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SaleController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    public function index(Request $request)
    {
        $sales = Sale::with(['items.product', 'customer'])
            ->dateRange($request->from, $request->to)
            ->orderByDesc('sale_date')
            ->paginate(20)
            ->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function create()
    {
        $products  = Product::active()
            ->where('stock_quantity', '>', 0)
            ->with('category')
            ->orderBy('name')
            ->get();

        $customers = Customer::active()->orderBy('name')->get();

        return view('sales.create', compact('products', 'customers'));
    }

    public function store(StoreSaleRequest $request)
    {
        try {
            $sale = $this->inventoryService->createSale($request->validated());

            ActivityLogger::created($sale, "Sale {$sale->reference} created — ₹{$sale->total_amount}");

            return redirect()->route('sales.show', $sale)
                ->with('success', "Invoice {$sale->reference} created. Stock updated.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }
    }

    public function show(Sale $sale)
    {
        $sale->load('items.product.category', 'customer');
        return view('sales.show', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        try {
            ActivityLogger::deleted($sale, "Sale {$sale->reference} cancelled — stock restored");
            $this->inventoryService->deleteSale($sale);

            return redirect()->route('sales.index')
                ->with('success', "Sale {$sale->reference} cancelled. Stock restored.");
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
