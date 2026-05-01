<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerDashboardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderTrackingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\SupplierDashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

require __DIR__ . '/auth.php';

// ════════════════════════════════════════════════════════════════
//  PUBLIC STOREFRONT (no auth)
// ════════════════════════════════════════════════════════════════
Route::prefix('shop')->name('shop.')->group(function () {
    Route::get('/',                 [StorefrontController::class, 'index'])->name('index');
    Route::get('/product/{product}',[StorefrontController::class, 'show'])->name('show');
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
//  AUTHENTICATED ROUTES
// ════════════════════════════════════════════════════════════════
Route::middleware(['auth'])->group(function () {

    // ── Dashboard ────────────────────────────────────────────────
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

    // ── Products ─────────────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::get('products/create',         [ProductController::class, 'create'])->name('products.create');
        Route::post('products',               [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('products/{product}',      [ProductController::class, 'update'])->name('products.update');
    });
    Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('role:admin');
    Route::get('products',           [ProductController::class, 'index'])->name('products.index');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');

    // ── Suppliers ────────────────────────────────────────────────
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

    // ── Customers ────────────────────────────────────────────────
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

    // ── Quotations ───────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('quotations/create',               [QuotationController::class, 'create'])->name('quotations.create');
        Route::post('quotations',                     [QuotationController::class, 'store'])->name('quotations.store');
        Route::post('quotations/{quotation}/convert', [QuotationController::class, 'convertToSale'])->name('quotations.convert');
        Route::post('quotations/{quotation}/status',  [QuotationController::class, 'updateStatus'])->name('quotations.status');
        Route::delete('quotations/{quotation}',       [QuotationController::class, 'destroy'])->name('quotations.destroy');
    });
    Route::get('quotations',             [QuotationController::class, 'index'])->name('quotations.index');
    Route::get('quotations/{quotation}', [QuotationController::class, 'show'])->name('quotations.show');

    // ── Sales ────────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive|customer')->group(function () {
        Route::get('sales/create', [SaleController::class, 'create'])->name('sales.create');
        Route::post('sales',       [SaleController::class, 'store'])->name('sales.store');
    });
    Route::middleware('role:admin|sales_executive|inventory_manager|viewer')->group(function () {
        Route::get('sales', [SaleController::class, 'index'])->name('sales.index');
    });
    Route::get('sales/{sale}',    [SaleController::class, 'show'])->name('sales.show');
    Route::delete('sales/{sale}', [SaleController::class, 'destroy'])->name('sales.destroy')->middleware('role:admin');

    Route::get('sales/{sale}/invoice/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::get('sales/{sale}/invoice/preview',  [InvoiceController::class, 'preview'])->name('invoices.preview');

    // ── Sale Returns ─────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('returns',               [SaleReturnController::class, 'index'])->name('returns.index');
        Route::get('returns/{sale}/create', [SaleReturnController::class, 'create'])->name('returns.create');
        Route::post('returns/{sale}',       [SaleReturnController::class, 'store'])->name('returns.store');
        Route::get('returns/{return}/show', [SaleReturnController::class, 'show'])->name('returns.show');
    });

    // ── Payments ─────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('payments',                  [PaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/receivables',      [PaymentController::class, 'receivables'])->name('payments.receivables');
        Route::post('payments/{sale}',          [PaymentController::class, 'store'])->name('payments.store');
        Route::delete('payments/{payment}',     [PaymentController::class, 'destroy'])->name('payments.destroy');
    });

    // ── Purchases ────────────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::get('purchases/create', [PurchaseController::class, 'create'])->name('purchases.create');
        Route::post('purchases',       [PurchaseController::class, 'store'])->name('purchases.store');
    });
    Route::delete('purchases/{purchase}', [PurchaseController::class, 'destroy'])->name('purchases.destroy')->middleware('role:admin');
    Route::middleware('role:admin|inventory_manager|viewer')->group(function () {
        Route::get('purchases',            [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/{purchase}', [PurchaseController::class, 'show'])->name('purchases.show');
    });

    // ── Purchase Returns ─────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::get('purchase-returns',                  [PurchaseReturnController::class, 'index'])->name('purchase-returns.index');
        Route::get('purchase-returns/{purchaseReturn}', [PurchaseReturnController::class, 'show'])->name('purchase-returns.show');
        Route::get('purchases/{purchase}/return',       [PurchaseReturnController::class, 'create'])->name('purchase-returns.create');
        Route::post('purchases/{purchase}/return',      [PurchaseReturnController::class, 'store'])->name('purchase-returns.store');
    });

    // ── Stock Adjustments ────────────────────────────────────────
    Route::middleware('role:admin|inventory_manager')->group(function () {
        Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index','create','store']);
    });

    // ── Expenses ─────────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::get('expenses',              [ExpenseController::class, 'index'])->name('expenses.index');
        Route::get('expenses/create',       [ExpenseController::class, 'create'])->name('expenses.create');
        Route::post('expenses',             [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('expenses/{expense}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');
    });

    // ── POS ──────────────────────────────────────────────────────
    Route::middleware('role:admin|sales_executive')->group(function () {
        Route::get('pos',  [PosController::class, 'index'])->name('pos.index');
        Route::post('pos', [PosController::class, 'sale'])->name('pos.sale');
    });

    // ── Reports ──────────────────────────────────────────────────
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('sales',     [ReportController::class, 'sales'])->name('sales')->middleware('role:admin|viewer');
        Route::get('purchases', [ReportController::class, 'purchases'])->name('purchases')->middleware('role:admin|inventory_manager|viewer');
        Route::get('profit',    [ReportController::class, 'profit'])->name('profit')->middleware('role:admin|viewer');
    });

    Route::prefix('export')->name('export.')->group(function () {
        Route::get('sales',     [ExportController::class, 'sales'])->name('sales')->middleware('role:admin|viewer');
        Route::get('purchases', [ExportController::class, 'purchases'])->name('purchases')->middleware('role:admin|inventory_manager|viewer');
        Route::get('profit',    [ExportController::class, 'profit'])->name('profit')->middleware('role:admin|viewer');
    });

    // ── Notifications ────────────────────────────────────────────
    Route::get('notifications',                        [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/{notification}/read',   [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::post('notifications/read-all',              [NotificationController::class, 'markAllRead'])->name('notifications.readAll');

    // ── Admin only ───────────────────────────────────────────────
    Route::middleware('role:admin')->group(function () {
        Route::resource('categories', CategoryController::class)->except(['show']);
        Route::resource('users',      UserController::class)->except(['show']);
        Route::get('activity-logs',   [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
