@extends('layouts.app')
@section('title','Add Seller')
@section('heading','Add Seller')
@section('header-actions')
    <a href="{{ route('sellers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-2xl">
<form method="POST" action="{{ route('sellers.store') }}" x-data="{login:false}">
@csrf
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Seller / Dealer Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
            <input type="text" name="phone" value="{{ old('phone') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Region / City</label>
            <input type="text" name="region" value="{{ old('region') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Credit Limit (₹)</label>
            <input type="number" name="credit_limit" value="{{ old('credit_limit', 0) }}" min="0"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Address</label>
            <textarea name="address" rows="2"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">{{ old('address') }}</textarea>
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
            <input type="text" name="notes" value="{{ old('notes') }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
        </div>
    </div>

    {{-- Login Access --}}
    <div class="p-6">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="create_login" value="1" id="create-login"
                   class="w-4 h-4 rounded text-green-600"
                   onchange="document.getElementById('login-fields').classList.toggle('hidden', !this.checked)">
            <span class="font-medium text-gray-700">Create login access for this seller</span>
        </label>
        <p class="text-xs text-gray-400 mt-1 ml-7">Seller will be able to log in and record their own sales</p>

        <div id="login-fields" class="hidden mt-4 grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Login Email *</label>
                <input type="email" name="login_email" value="{{ old('login_email') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
                @error('login_email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                <input type="password" name="login_password" minlength="8"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-green-400">
                @error('login_password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('sellers.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-green-600 text-white rounded-lg font-medium hover:bg-green-700 transition">
            Add Seller
        </button>
    </div>
</div>
</form>
</div>
@endsection
