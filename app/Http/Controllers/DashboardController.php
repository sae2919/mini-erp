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
        $request = request(); // ✅ ADDED (fix for filter + request issue)

        $user = auth()->user();

        if ($user->hasRole('seller')) {
            return $this->sellerDashboard($user, $request); // ✅ pass request
        }

        return $this->adminDashboard($request); // ✅ pass request
    }

    private function adminDashboard($request)
    {
        // ✅ ADDED FILTERS
        $from = $request->from 
            ? Carbon::parse($request->from)->startOfDay() 
            : Carbon::now()->startOfMonth();

        $to = $request->to 
            ? Carbon::parse($request->to)->endOfDay() 
            : Carbon::now()->endOfMonth();

        $thisMonth = Carbon::now()->startOfMonth();

        $totalProducts      = Product::active()->count();
        $warehouseStock     = Product::active()->sum('stock_quantity');
        $lowStockCount      = Product::active()->lowStock()->count();

        // ✅ FIXED (FILTER ADDED)
        $totalDispatched = DispatchOrder::where('status','!=','cancelled')
            ->whereBetween('dispatch_date', [$from, $to])
            ->sum('total_amount');

        // ✅ FIXED (FILTER ADDED)
        $totalCollected = SellerPayment::whereBetween('paid_at', [$from, $to])
            ->sum('amount');

        // ✅ FIXED (CONSISTENT FILTER)
        $outstandingBalance = DispatchOrder::where('status','!=','cancelled')
            ->whereBetween('dispatch_date', [$from, $to])
            ->sum(DB::raw('total_amount - paid_amount'));

        // ✅ NEW (TOTAL SALES)
        $totalSales = SellerSale::whereBetween('sale_date', [$from, $to])
            ->sum('total_amount');

        // ✅ FIXED (FILTER)
        $productionCost = Production::whereBetween('production_date', [$from, $to])
            ->sum('total_cost');

        // ✅ NEW (PROFIT)
        $profit = $totalSales - $productionCost;

        $totalCommissions   = Commission::sum('amount');
        $pendingCommissions = Commission::where('status','pending')->sum('amount');

        $dispatchThisMonth  = DispatchOrder::where('dispatch_date','>=',$thisMonth)->sum('total_amount');
        $salesThisMonth     = SellerSale::where('sale_date','>=',$thisMonth)->sum('total_amount');
        $productionThisMonth= Production::where('production_date','>=',$thisMonth)->sum('total_cost');

        // ✅ FIXED (FILTER INSIDE RELATION)
        $topSellers = Seller::withSum(['sales as total_sales' => function ($q) use ($from, $to) {
                $q->whereBetween('sale_date', [$from, $to]);
            }], 'total_amount')
            ->withCount('sales')
            ->orderByDesc('total_sales')
            ->take(5)
            ->get();

        // ✅ IMPROVED MONTHLY DATA (CONSISTENT)
        $monthlyData = collect(range(11,0))->map(function($m) {
            $date = Carbon::now()->startOfMonth()->subMonths($m);

            return [
                'month'      => $date->format('M Y'),

                'dispatched' => DispatchOrder::whereYear('dispatch_date',$date->year)
                    ->whereMonth('dispatch_date',$date->month)
                    ->where('status','!=','cancelled')
                    ->sum('total_amount'),

                'collected'  => SellerPayment::whereYear('paid_at',$date->year)
                    ->whereMonth('paid_at',$date->month)
                    ->sum('amount'),

                'sold'       => SellerSale::whereYear('sale_date',$date->year)
                    ->whereMonth('sale_date',$date->month)
                    ->sum('total_amount'),
            ];
        });

        $lowStockProducts = Product::active()->lowStock()->with('category')->orderBy('stock_quantity')->take(6)->get();
        $recentDispatches = DispatchOrder::with('seller')->latest()->take(5)->get();
        $notifications    = ErpNotification::forUser(auth()->id())->unread()->latest()->take(5)->get();

        return view('dashboard.index', compact(
            'from','to', // ✅ NEW

            'totalProducts','warehouseStock','lowStockCount',
            'totalDispatched','totalCollected','outstandingBalance',

            'totalSales','productionCost','profit', // ✅ NEW

            'totalCommissions','pendingCommissions',
            'dispatchThisMonth','salesThisMonth','productionThisMonth',
            'topSellers','monthlyData','lowStockProducts',
            'recentDispatches','notifications'
        ));
    }

    private function sellerDashboard($user, $request) // ✅ added request (no logic change)
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
                'sales' => $seller->sales()
                    ->whereYear('sale_date',$date->year)
                    ->whereMonth('sale_date',$date->month)
                    ->sum('total_amount'),
            ];
        });

        return view('dashboard.seller', compact(
            'seller','myStock','mySalesTotal','myCommission','myBalanceDue',
            'salesThisMonth','recentSales','recentDispatches','monthlySales'
        ));
    }
}