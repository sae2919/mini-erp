@extends('layouts.app')
@section('title', 'Add Supplier')
@section('heading', 'Add Supplier')
@section('header-actions')
    <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection
@section('content')
<div class="py-4 max-w-xl">
<form method="POST" action="{{ route('suppliers.store') }}">
@csrf
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Supplier Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea name="address" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">{{ old('address') }}</textarea>
        </div>

    </div>
    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('suppliers.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            Save Supplier
        </button>
    </div>
</div>
</form>
</div>
@endsection
