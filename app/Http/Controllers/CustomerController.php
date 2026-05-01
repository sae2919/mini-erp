<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::withCount([
    'sales as total_orders',
])->withSum('sales as total_spent', 'total_amount')
->when($request->search, fn($q) => $q->where(function($q2) use ($request) {
    $q2->where('name', 'like', '%'.$request->search.'%')
       ->orWhere('email', 'like', '%'.$request->search.'%')
       ->orWhere('phone', 'like', '%'.$request->search.'%');
}))
->orderBy('name')
->paginate(20)
->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = Customer::create($validated);

        ActivityLogger::created($customer, "Customer \"{$customer->name}\" created");

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$customer->name}\" added.");
    }

    public function show(Customer $customer)
    {
        $sales = $customer->sales()
            ->with('items')
            ->orderByDesc('sale_date')
            ->paginate(10);

        return view('customers.show', compact('customer', 'sales'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name'    => ['required', 'string', 'max:255'],
            'phone'   => ['nullable', 'string', 'max:20'],
            'email'   => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes'   => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update($validated);

        ActivityLogger::updated($customer, "Customer \"{$customer->name}\" updated");

        return redirect()->route('customers.index')
            ->with('success', "Customer updated.");
    }

    public function destroy(Customer $customer)
    {
        ActivityLogger::deleted($customer, "Customer \"{$customer->name}\" deleted");
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Customer deleted.");
    }
}
