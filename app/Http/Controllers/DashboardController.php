<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\DispatchOrder;
use App\Models\ErpNotification;
use App\Models\Product;
use App\Models\Production;
use App\Models\Seller;
use App\Models\SellerSale;
use App\Models\SellerPayment;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->hasRole('seller')) {
            return $this->sellerDashboard($user);
        }

        return $this->adminDashboard();
    }

    private function adminDashboard()
    {
        $thisMonth = Carbon::now()->startOfMonth();

        $totalProducts      = Product::active()->count();
        $warehouseStock     = Product::active()->sum('stock_quantity');
        $lowStockCount      = Product::active()->lowStock()->count();
        $totalDispatched    = DispatchOrder::where('status','!=','cancelled')->sum('total_amount');
        $totalCollected     = SellerPayment::sum('amount');
        $outstandingBalance = DispatchOrder::where('payment_status','!=','paid')
            ->where('status','!=','cancelled')
            ->sum(DB::raw('total_amount - paid_amount'));
        $totalCommissions   = Commission::sum('amount');
        $pendingCommissions = Commission::where('status','pending')->sum('amount');
        $dispatchThisMonth  = DispatchOrder::where('dispatch_date','>=',$thisMonth)->sum('total_amount');
        $salesThisMonth     = SellerSale::where('sale_date','>=',$thisMonth)->sum('total_amount');
        $productionThisMonth= Production::where('production_date','>=',$thisMonth)->sum('total_cost');

        $topSellers = Seller::withSum('sales as total_sales','total_amount')
            ->withCount('sales')->orderByDesc('total_sales')->take(5)->get();

        $monthlyData = collect(range(11,0))->map(function($m) {
            $date = Carbon::now()->startOfMonth()->subMonths($m);
            return [
                'month'      => $date->format('M Y'),
                'dispatched' => DispatchOrder::whereYear('dispatch_date',$date->year)->whereMonth('dispatch_date',$date->month)->sum('total_amount'),
                'collected'  => SellerPayment::whereYear('paid_at',$date->year)->whereMonth('paid_at',$date->month)->sum('amount'),
                'sold'       => SellerSale::whereYear('sale_date',$date->year)->whereMonth('sale_date',$date->month)->sum('total_amount'),
            ];
        });

        $lowStockProducts = Product::active()->lowStock()->with('category')->orderBy('stock_quantity')->take(6)->get();
        $recentDispatches = DispatchOrder::with('seller')->latest()->take(5)->get();
        $notifications    = ErpNotification::forUser(auth()->id())->unread()->latest()->take(5)->get();

        return view('dashboard.index', compact(
            'totalProducts','warehouseStock','lowStockCount',
            'totalDispatched','totalCollected','outstandingBalance',
            'totalCommissions','pendingCommissions',
            'dispatchThisMonth','salesThisMonth','productionThisMonth',
            'topSellers','monthlyData','lowStockProducts',
            'recentDispatches','notifications'
        ));
    }

    private function sellerDashboard($user)
    {
        $seller     = Seller::where('user_id',$user->id)->firstOrFail();
        $thisMonth  = Carbon::now()->startOfMonth();

        $myStock        = $seller->stocks()->with('product.category')->get();
        $mySalesTotal   = $seller->sales()->sum('total_amount');
        $myCommission   = $seller->commissions()->where('status','pending')->sum('amount');
        $myBalanceDue   = max(0, $seller->balance_due);
        $salesThisMonth = $seller->sales()->where('sale_date','>=',$thisMonth)->sum('total_amount');
        $recentSales    = $seller->sales()->with('items.product')->latest()->take(5)->get();
        $recentDispatches = $seller->dispatchOrders()->with('items.product')->latest()->take(3)->get();

        $monthlySales = collect(range(5,0))->map(function($m) use ($seller) {
            $date = Carbon::now()->startOfMonth()->subMonths($m);
            return [
                'month' => $date->format('M'),
                'sales' => $seller->sales()->whereYear('sale_date',$date->year)->whereMonth('sale_date',$date->month)->sum('total_amount'),
            ];
        });

        return view('dashboard.seller', compact(
            'seller','myStock','mySalesTotal','myCommission','myBalanceDue',
            'salesThisMonth','recentSales','recentDispatches','monthlySales'
        ));
    }
}
