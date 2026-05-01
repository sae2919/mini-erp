@extends('layouts.app')
@section('title', 'Add User')
@section('heading', 'Add User')
@section('header-actions')
    <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-lg">
<form method="POST" action="{{ route('users.store') }}">
@csrf
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent @error('name') border-red-400 @enderror">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent @error('email') border-red-400 @enderror">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
            <input type="password" name="password" required minlength="8"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent @error('password') border-red-400 @enderror">
            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
            <input type="password" name="password_confirmation" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        {{-- Role selection --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
            <div class="space-y-2">

                @php
                $roleInfo = [
                    'admin' => [
                        'emoji' => '🥇',
                        'title' => 'Admin',
                        'desc'  => 'Full access — products, suppliers, purchases, sales, reports, users, activity log.',
                        'color' => 'border-yellow-300 bg-yellow-50',
                    ],
                    'inventory_manager' => [
                        'emoji' => '🥈',
                        'title' => 'Inventory Manager',
                        'desc'  => 'Manages stock — add purchases, edit products (no pricing), stock adjustments, purchase report. Cannot see profit.',
                        'color' => 'border-blue-200 bg-blue-50',
                    ],
                    'sales_executive' => [
                        'emoji' => '🥉',
                        'title' => 'Sales Executive',
                        'desc'  => 'Creates sales only — can view products (no cost price) and manage customers. No access to purchases or reports.',
                        'color' => 'border-green-200 bg-green-50',
                    ],
                    'viewer' => [
                        'emoji' => '👁',
                        'title' => 'Viewer / Accountant',
                        'desc'  => 'Read-only access — view dashboard, all reports including profit. Cannot create or modify anything.',
                        'color' => 'border-gray-200 bg-gray-50',
                    ],
                ];
                @endphp

                @foreach($roles as $role)
                @php $info = $roleInfo[$role->name] ?? ['emoji' => '?', 'title' => $role->name, 'desc' => '', 'color' => 'border-gray-200']; @endphp
                <label class="flex items-start gap-3 p-3 border rounded-lg cursor-pointer hover:bg-gray-50 transition {{ $info['color'] }}">
                    <input type="radio" name="role" value="{{ $role->name }}"
                           {{ old('role', 'sales_executive') === $role->name ? 'checked' : '' }}
                           class="mt-0.5 text-indigo-600">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ $info['emoji'] }} {{ $info['title'] }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $info['desc'] }}</p>
                    </div>
                </label>
                @endforeach
            </div>
            @error('role')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="px-6 py-4 flex justify-end gap-3">
        <a href="{{ route('users.index') }}"
           class="px-4 py-2 text-sm border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">Cancel</a>
        <button type="submit"
                class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg font-medium hover:bg-indigo-700 transition">
            Create User
        </button>
    </div>
</div>
</form>
</div>
@endsection
