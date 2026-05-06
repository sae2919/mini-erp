<?php

use App\Http\Controllers\StockRequestController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\SellerDispatchController;
use App\Http\Controllers\SellerPosController;
use App\Http\Controllers\SellerSaleController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

// ════════════════════════════════════════════════════════════════
//  PUBLIC STOREFRONT  (no auth required)
// ════════════════════════════════════════════════════════════════
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/',                  [StorefrontController::class, 'index'])->name('index');
    Route::get('/product/{product}', [StorefrontController::class, 'show'])->name('show');
});

Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',               [CartController::class, 'index'])->name('index');
    Route::post('/add/{product}', [CartController::class, 'add'])->name('add');
    Route::post('/update',        [CartController::class, 'update'])->name('update');
    Route::delete('/remove/{id}', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear',         [CartController::class, 'clear'])->name('clear');
});

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/',  [CheckoutController::class, 'index'])->name('index');
    Route::post('/', [CheckoutController::class, 'store'])->name('store');
});

Route::get('/track',             [OrderTrackingController::class, 'search'])->name('orders.search');
Route::post('/track',            [OrderTrackingController::class, 'search'])->name('orders.search.post');
Route::get('/track/{reference}', [OrderTrackingController::class, 'track'])->name('orders.track');

// ════════════════════════════════════════════════════════════════
//  ALL AUTHENTICATED ROUTES — single top-level auth group
//  Everything below requires login. No exceptions.
// ════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {

    // ── Dashboard ─────────────────────────────────────────────────
    Route::get('/', function () {
        if (auth()->user()->hasRole('customer')) {
            return redirect()->route('products.index');
        }
        return app(DashboardController::class)->index();
    })->name('dashboard');

    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::get('/dashboard/suppliers', [SupplierDashboardController::class, 'index'])->name('dashboard.suppliers');
    });

    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('/dashboard/customers', [CustomerDashboardController::class, 'index'])->name('dashboard.customers');
    });

    // ── Products ──────────────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager|manager')->group(function () {
        Route::get('products/create',         [ProductController::class, 'create'])->name('products.create');
        Route::post('products',               [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}',      [ProductController::class, 'update'])->name('products.update');
    });
    Route::middleware('role:admin')->group(function () {
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    });
    // List & detail accessible to all authenticated roles
    Route::get('products',              [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}',    [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/history', [ProductController::class, 'stockHistory'])->name('products.history');

    // ── Productions ───────────────────────────────────────────────
    Route::middleware('role:admin|manager|inventory_manager')->group(function () {
        Route::get('productions/create',          [ProductionController::class, 'create'])->name('productions.create');
        Route::post('productions',                [ProductionController::class, 'store'])->name('productions.store');
        Route::delete('productions/{production}', [ProductionController::class, 'destroy'])->name('productions.destroy');
    });
    Route::middleware('role:admin|manager|inventory_manager|viewer')->group(function () {
        Route::get('productions',              [ProductionController::class, 'index'])->name('productions.index');
        Route::get('productions/{production}', [ProductionController::class, 'show'])->name('productions.show');
    });

    // ── Suppliers ─────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('suppliers/create',          [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('suppliers',                [SupplierController::class, 'store'])->name('suppliers.store');
        Route::get('suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('suppliers/{supplier}',      [SupplierController::class, 'update'])->name('suppliers.update');
        Route::delete('suppliers/{supplier}',   [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });
    Route::middleware('role:admin|inventory_manager|viewer')->group(function () {
        Route::get('suppliers',            [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show');
    });

    // ── Sellers ───────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('sellers/create',        [SellerController::class, 'create'])->name('sellers.create');
        Route::post('sellers',              [SellerController::class, 'store'])->name('sellers.store');
        Route::get('sellers/{seller}/edit', [SellerController::class, 'edit'])->name('sellers.edit');
        Route::put('sellers/{seller}',      [SellerController::class, 'update'])->name('sellers.update');
    });
    Route::middleware('role:admin|sales_executive|manager|inventory_manager|viewer')->group(function () {
        Route::get('sellers',          [SellerController::class, 'index'])->name('sellers.index');
        Route::get('sellers/{seller}', [SellerController::class, 'show'])->name('sellers.show');
    });

    // ── Dispatch Orders ───────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('dispatches/create',              [DispatchController::class, 'create'])->name('dispatches.create');
        Route::post('dispatches',                    [DispatchController::class, 'store'])->name('dispatches.store');
        Route::post('dispatches/{dispatch}/payment', [DispatchController::class, 'recordPayment'])->name('dispatches.payment');
    });
    Route::middleware('role:admin|sales_executive|manager|inventory_manager|viewer')->group(function () {
        Route::get('dispatches',            [DispatchController::class, 'index'])->name('dispatches.index');
        Route::get('dispatches/{dispatch}', [DispatchController::class, 'show'])->name('dispatches.show');
    });

    // ── Seller Sales ──────────────────────────────────────────────
    Route::middleware('role:seller')->group(function () {
        Route::get('my-sales/create', [SellerSaleController::class, 'create'])->name('seller-sales.create');
        Route::post('my-sales',       [SellerSaleController::class, 'store'])->name('seller-sales.store');
    });
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('seller-sales/create', [SellerSaleController::class, 'create'])->name('seller-sales.create.admin');
        Route::post('seller-sales',       [SellerSaleController::class, 'store'])->name('seller-sales.store.admin');
    });
    Route::get('seller-sales',              [SellerSaleController::class, 'index'])->name('seller-sales.index');
    Route::get('seller-sales/{sellerSale}', [SellerSaleController::class, 'show'])->name('seller-sales.show');

    // ── Commissions ───────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('commissions',                         [CommissionController::class, 'index'])->name('commissions.index');
        Route::post('commissions/{commission}/mark-paid', [CommissionController::class, 'markPaid'])->name('commissions.mark-paid');
        Route::post('commissions/payout-seller',          [CommissionController::class, 'payoutSeller'])->name('commissions.payout-seller');
        Route::post('commissions/payout-all',             [CommissionController::class, 'payoutAll'])->name('commissions.payout-all');
    });

    // ── Seller POS ────────────────────────────────────────────────
    Route::middleware('role:seller|admin|sales_executive')->group(function () {
        Route::get('seller-pos',  [SellerPosController::class, 'index'])->name('seller-pos.index');
        Route::post('seller-pos', [SellerPosController::class, 'sale'])->name('seller-pos.sale');
    });

    // ── Seller Dispatch History ───────────────────────────────────
    Route::middleware('role:seller')->group(function () {
        Route::get('my-dispatches', [SellerDispatchController::class, 'index'])->name('seller-dispatches.index');
    });

    // ── My Stock Requests (seller self-service) ───────────────────
    // FIX: was outside auth middleware — anyone could access it
    Route::get('my-stock-requests', [StockRequestController::class, 'myRequests'])
        ->name('stock-requests.my');

    // ── Customers ─────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('customers/create',          [CustomerController::class, 'create'])->name('customers.create');
        Route::post('customers',                [CustomerController::class, 'store'])->name('customers.store');
        Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
        Route::put('customers/{customer}',      [CustomerController::class, 'update'])->name('customers.update');
        Route::delete('customers/{customer}',   [CustomerController::class, 'destroy'])->name('customers.destroy');
    });
    Route::middleware('role:admin|sales_executive|viewer')->group(function () {
        Route::get('customers',            [CustomerController::class, 'index'])->name('customers.index');
        Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    });

    // ── Quotations ────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('quotations/create',               [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('quotations',                     [QuotationController::class, 'store'])->name('quotations.store');
        Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToSale'])->name('quotations.convert');
        Route::post('quotations/{quotation}/status',  [QuotationController::class, 'updateStatus'])->name('quotations.status');
        Route::delete('quotations/{quotation}',       [QuotationController::class, 'destroy'])->name('quotations.destroy');
    });
    Route::get('quotations',             [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');

    // ── Sales ─────────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive|customer')->group(function () {
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales',       [SaleController::class, 'store'])->name('sales.store');
    });
    Route::middleware('role:admin|sales_executive|inventory_manager|viewer')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    });
    Route::get('sales/{sale}',    [SaleController::class, 'show'])->name('sales.show');
    Route::middleware('role:admin')->group(function () {
        Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy');
    });

    Route::get('sales/{sale}/invoice/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('sales/{sale}/invoice/preview',  [InvoiceController::class, 'preview'])->name('invoices.preview');

    // ── Sale Returns ──────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('returns',               [SaleReturnController::class, 'index'])->name('returns.index');
        Route::get('returns/{sale}/create', [SaleReturnController::class, 'create'])->name('returns.create');
        Route::post('returns/{sale}',       [SaleReturnController::class, 'store'])->name('returns.store');
        Route::get('returns/{return}/show', [SaleReturnController::class, 'show'])->name('returns.show');
    });

    // ── Payments ──────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('payments',             [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/receivables', [PaymentController::class, 'receivables'])->name('payments.receivables');
        Route::post('payments/{sale}',     [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('payments/{payment}',[PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // ── Purchases ─────────────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases',       [PurchaseController::class, 'store'])->name('purchases.store');
    });
    Route::middleware('role:admin')->group(function () {
        Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy');
    });
    Route::middleware('role:admin|inventory_manager|viewer')->group(function () {
        Route::get('purchases',            [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    // ── Purchase Returns ──────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::get('purchase-returns',                  [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
        Route::get('purchases/{purchase}/return',       [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::post('purchases/{purchase}/return',      [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
    });

    // ── Stock Adjustments ─────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store']);
    });

    // ── Stock Requests ────────────────────────────────────────────
    // FIX: was split across two auth groups causing duplicate route names.
    // Consolidated here with proper role guards.
    // ── Stock Requests ────────────────────────────────────────────
Route::middleware('role:admin|inventory_manager')->group(function () {
    Route::get('admin/stock-requests',           [StockRequestController::class, 'adminIndex'])->name('stock-requests.admin');
    Route::post('stock-requests/{id}/approve',   [StockRequestController::class, 'approve'])->name('stock-requests.approve');
    Route::post('stock-requests/{id}/reject',    [StockRequestController::class, 'reject'])->name('stock-requests.reject');
});

// ✅ FIXED HERE ONLY (added seller role)
Route::middleware('role:admin|seller')->group(function () {
    Route::post('stock-requests/{id}/pay', [StockRequestController::class, 'pay'])->name('stock-requests.pay');
});

// The resource itself (index, create, store, show)
Route::resource('stock-requests', StockRequestController::class)->except(['destroy']);

    // ── Expenses ──────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('expenses',             [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create',      [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses',            [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('expenses/{expense}',[ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // ── POS ───────────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('pos',  [PosController::class, 'index'])->name('pos.index');
        Route::post('pos', [PosController::class, 'sale'])->name('pos.sale');
    });

    // ── Reports ───────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('sales',     [ReportController::class, 'sales'])->name('sales')
            ->middleware('role:admin|viewer');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases')
            ->middleware('role:admin|inventory_manager|viewer');
        Route::get('profit',    [ReportController::class, 'profit'])->name('profit')
            ->middleware('role:admin|viewer');

        Route::middleware('role:admin|viewer')->group(function () {
            Route::get('seller-pnl',         [ReportController::class, 'sellerPnl'])->name('seller-pnl');
            Route::get('account-statement',  [ReportController::class, 'accountStatement'])->name('account-statement');
            Route::get('best-products',      [ReportController::class, 'bestProducts'])->name('best-products');
            Route::get('seller-performance', [ReportController::class, 'sellerPerformance'])->name('seller-performance');
            Route::get('stock-movement',     [ReportController::class, 'stockMovement'])->name('stock-movement');
            Route::get('commission',         [ReportController::class, 'commission'])->name('commission');
        });
    });

    // ── Exports ───────────────────────────────────────────────────
    Route::prefix('export')->name('export.')->group(function () {
        Route::get('sales',     [ExportController::class, 'sales'])->name('sales')
            ->middleware('role:admin|viewer');
        Route::get('purchases', [ExportController::class, 'purchases'])->name('purchases')
            ->middleware('role:admin|inventory_manager|viewer');
        Route::get('profit',    [ExportController::class, 'profit'])->name('profit')
            ->middleware('role:admin|viewer');
    });

    // ── Notifications ─────────────────────────────────────────────
    Route::get('notifications',                      [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all',            [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // ── Admin-only ────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories',   CategoryController::class)->except(['show']);
        Route::resource('users',        UserController::class)->except(['show']);
        Route::get('activity-logs',     [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
    // ── Exports ───────────────────────────────────────────────────
Route::prefix('export')->name('export.')->group(function () {

    Route::get('sales',     [ExportController::class, 'sales'])->name('sales')
        ->middleware('role:admin|viewer');

    Route::get('purchases', [ExportController::class, 'purchases'])->name('purchases')
        ->middleware('role:admin|inventory_manager|viewer');

    Route::get('profit',    [ExportController::class, 'profit'])->name('profit')
        ->middleware('role:admin|viewer');

    // 🔥 YOUR REPORT EXPORTS (ADDED PROPERLY HERE)
    Route::get('seller-pl', [ReportController::class, 'exportSellerPL'])->name('seller-pl');
    Route::get('account-statement', [ReportController::class, 'exportAccount'])->name('account-statement');
    Route::get('best-products', [ReportController::class, 'exportProducts'])->name('best-products');
    Route::get('seller-performance', [ReportController::class, 'exportPerformance'])->name('seller-performance');

});
Route::get('/export/stock-report', [ProductionController::class, 'exportStockReport'])
    ->name('export.stock-report');
}); // end auth middleware group