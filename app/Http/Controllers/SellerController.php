<?php

namespace App\Http\Controllers;

use App\Models\Seller;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $sellers = Seller::with('user')
            ->withSum('sales as total_sales','total_amount')
            ->withCount('sales')
            ->when($request->search, fn($q) => $q->where(fn($q2) =>
                $q2->where('name','like','%'.$request->search.'%')
                   ->orWhere('region','like','%'.$request->search.'%')
            ))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('sellers.index', compact('sellers'));
    }

    public function create()
    {
        return view('sellers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => ['required','string','max:255'],
            'phone'          => ['nullable','string','max:20'],
            'email'          => ['nullable','email'],
            'address'        => ['nullable','string'],
            'region'         => ['nullable','string','max:100'],
            'credit_limit'   => ['nullable','numeric','min:0'],
            'notes'          => ['nullable','string'],
            'create_login'   => ['nullable','boolean'],
            'login_email'    => ['required_if:create_login,1','nullable','email','unique:users,email'],
            'login_password' => ['required_if:create_login,1','nullable','min:8'],
        ]);

        $userId = null;
        if ($request->create_login) {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->login_email,
                'password' => Hash::make($request->login_password),
            ]);
            $user->assignRole('seller');
            $userId = $user->id;
        }

        $seller = Seller::create([
            'user_id'      => $userId,
            'name'         => $request->name,
            'phone'        => $request->phone,
            'email'        => $request->email ?? $request->login_email,
            'address'      => $request->address,
            'region'       => $request->region,
            'credit_limit' => $request->credit_limit ?? 0,
            'notes'        => $request->notes,
        ]);

        ActivityLogger::created($seller,
            "Seller \"{$seller->name}\" added".($request->create_login ? ' with login access' : '')
        );

        return redirect()->route('sellers.index')
            ->with('success','Seller added'.($request->create_login ? ' with login access.' : '.'));
    }

    public function show(Seller $seller)
    {
        $seller->load([
            'stocks.product',
            'dispatchOrders' => fn($q)=>$q->latest()->take(10),
            'sales'          => fn($q)=>$q->latest()->take(10),
            'payments'       => fn($q)=>$q->latest()->take(5),
        ]);
        $totalDispatched = $seller->dispatchOrders()->sum('total_amount');
        $totalPaid       = $seller->payments()->sum('amount');
        $totalSales      = $seller->sales()->sum('total_amount');
        $totalCommission = $seller->commissions()->sum('amount');
        $pendingComm     = $seller->commissions()->where('status','pending')->sum('amount');
        return view('sellers.show', compact('seller','totalDispatched','totalPaid','totalSales','totalCommission','pendingComm'));
    }

    public function edit(Seller $seller)
    {
        return view('sellers.edit', compact('seller'));
    }

    public function update(Request $request, Seller $seller)
    {
        $request->validate([
            'name'         => ['required','string','max:255'],
            'phone'        => ['nullable','string','max:20'],
            'email'        => ['nullable','email'],
            'address'      => ['nullable','string'],
            'region'       => ['nullable','string','max:100'],
            'credit_limit' => ['nullable','numeric','min:0'],
            'is_active'    => ['nullable'],
            'notes'        => ['nullable','string'],
        ]);

        $seller->update([
            ...$request->only(['name','phone','email','address','region','credit_limit','notes']),
            'is_active' => $request->has('is_active'),
        ]);

        ActivityLogger::updated($seller, "Seller \"{$seller->name}\" updated");

        return redirect()->route('sellers.show',$seller)->with('success','Seller updated.');
    }
}