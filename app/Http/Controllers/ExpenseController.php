<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with(['category', 'user'])
            ->dateRange($request->from, $request->to)
            ->when($request->category_id, fn($q) => $q->where('expense_category_id', $request->category_id))
            ->orderByDesc('expense_date')
            ->paginate(20)
            ->withQueryString();

        $categories  = ExpenseCategory::orderBy('name')->get();
        $totalAmount = Expense::dateRange($request->from, $request->to)->sum('amount');

        $byCategory = Expense::with('category')
            ->dateRange($request->from, $request->to)
            ->selectRaw('expense_category_id, SUM(amount) as total')
            ->groupBy('expense_category_id')
            ->get();

        return view('expenses.index', compact('expenses', 'categories', 'totalAmount', 'byCategory'));
    }

    public function create()
    {
        $categories = ExpenseCategory::orderBy('name')->get();
        return view('expenses.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'title'               => ['required', 'string', 'max:255'],
            'amount'              => ['required', 'numeric', 'min:0.01'],
            'expense_date'        => ['required', 'date'],
            'payment_method'      => ['required', 'in:cash,upi,card,bank_transfer,cheque'],
            'reference'           => ['nullable', 'string', 'max:255'],
            'notes'               => ['nullable', 'string', 'max:1000'],
        ]);

        $receipt = null;
        if ($request->hasFile('receipt')) {
            $receipt = $request->file('receipt')->store('receipts', 'public');
        }

        $expense = Expense::create([...$validated, 'user_id' => auth()->id(), 'receipt' => $receipt]);
        ActivityLogger::created($expense, "Expense \"{$expense->title}\" ₹{$expense->amount} added");

        return redirect()->route('expenses.index')->with('success', 'Expense recorded.');
    }

    public function destroy(Expense $expense)
    {
        ActivityLogger::deleted($expense, "Expense \"{$expense->title}\" deleted");
        $expense->delete();
        return back()->with('success', 'Expense deleted.');
    }
}
