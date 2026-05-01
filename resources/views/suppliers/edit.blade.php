@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('heading', 'Edit Supplier')
@section('header-actions')
    <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection
@section('content')
<div class="py-4 max-w-xl">
<form method="POST" action="{{ route('suppliers.update', $supplier) }}">
@csrf @method('PUT')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name *</label>
            <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea name="address" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('address', $supplier->address) }}</textarea>
        </div>

    </div>
    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('suppliers.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            Update Supplier
        </button>
    </div>
</div>
</form>
</div>
@endsection
