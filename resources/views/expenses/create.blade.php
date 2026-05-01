@extends('layouts.app')
@section('title', 'Add Expense')
@section('heading', 'Add Expense')
@section('header-actions')
    <a href="{{ route('expenses.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-xl">
<form method="POST" action="{{ route('expenses.store') }}" enctype="multipart/form-data">
@csrf
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Title *</label>
            <input type="text" name="title" value="{{ old('title') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                <select name="expense_category_id" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                    <option value="">-- Select --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('expense_category_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Amount (₹) *</label>
                <input type="number" name="amount" value="{{ old('amount') }}" step="0.01" min="0.01" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                <input type="date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                <select name="payment_method" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
                    <option value="cash">💵 Cash</option>
                    <option value="upi">📱 UPI</option>
                    <option value="card">💳 Card</option>
                    <option value="bank_transfer">🏦 Bank Transfer</option>
                    <option value="cheque">📝 Cheque</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Reference / Transaction ID</label>
            <input type="text" name="reference" value="{{ old('reference') }}"
                   placeholder="UPI ID, cheque no., etc."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Receipt (optional)</label>
            <input type="file" name="receipt" accept="image/*,.pdf"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <textarea name="notes" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('notes') }}</textarea>
        </div>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('expenses.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-red-600 text-white rounded-lg font-medium hover:bg-red-700 transition">
            Record Expense
        </button>
    </div>
</div>
</form>
</div>
@endsection
