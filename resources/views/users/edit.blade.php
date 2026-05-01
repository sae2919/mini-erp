@extends('layouts.app')
@section('title', 'Edit User')
@section('heading', 'Edit User')
@section('header-actions')
    <a href="{{ route('users.index') }}" class="text-sm text-gray-500 hover:text-gray-700">← Back</a>
@endsection

@section('content')
<div class="py-4 max-w-lg">
<form method="POST" action="{{ route('users.update', $user) }}">
@csrf @method('PUT')
<div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-100">
    <div class="p-6 space-y-4">

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
            <input type="password" name="password" minlength="8"
                   placeholder="Leave blank to keep current password"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
            <input type="password" name="password_confirmation"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-400 focus:border-transparent">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
            <div class="space-y-2">
                @foreach($roles as $role)
                <label class="flex items-start gap-3 p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition">
                    <input type="radio" name="role" value="{{ $role->name }}"
                           {{ old('role', $user->roles->first()?->name) === $role->name ? 'checked' : '' }}
                           class="mt-0.5 text-indigo-600">
                    <div>
                        <p class="text-sm font-medium text-gray-800">{{ ucfirst($role->name) }}</p>
                        <p class="text-xs text-gray-500">
                            @if($role->name === 'admin')
                                Full access — products, suppliers, purchases, sales, reports, users
                            @else
                                Can create sales and view reports. Cannot manage inventory.
                            @endif
                        </p>
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
            Update User
        </button>
    </div>
</div>
</form>
</div>
@endsection
