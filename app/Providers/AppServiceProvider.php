<?php

namespace App\Providers;
use Illuminate\Database\Eloquent\Model;
use App\Observers\ActivityObserver;
use App\Models\Sale;
use App\Models\SellerSale;
use App\Models\Customer;
use App\Models\Product;
use App\Models\StockRequest;
use App\Models\DispatchOrder;
use App\Models\Payment;



use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
{
    Sale::observe(ActivityObserver::class);
    SellerSale::observe(ActivityObserver::class);
    Customer::observe(ActivityObserver::class);
    Product::observe(ActivityObserver::class);
    StockRequest::observe(ActivityObserver::class);
    DispatchOrder::observe(ActivityObserver::class);
    Payment::observe(ActivityObserver::class);
    \Illuminate\Pagination\Paginator::defaultView('pagination::tailwind');
}
}
