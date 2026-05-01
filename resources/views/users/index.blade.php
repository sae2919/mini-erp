@extends('layouts.app')
@section('title', 'Users')
@section('heading', 'User Management')

@section('header-actions')
    <a href="{{ route('users.create') }}"
       class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-700 transition">
        + Add User
    </a>
@endsection

@section('content')
<div class="py-4">
<div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr class="text-left text-gray-500 font-medium">
                <th class="px-4 py-3">Name</th>
                <th class="px-4 py-3">Email</th>
                <th class="px-4 py-3">Role</th>
                <th class="px-4 py-3">Joined</th>
                <th class="px-4 py-3">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50 transition">
                <td class="px-4 py-3 font-medium text-gray-800">
                    {{ $user->name }}
                    @if($user->id === auth()->id())
                        <span class="text-xs text-indigo-500">(you)</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600">{{ $user->email }}</td>
                <td class="px-4 py-3">
                    @foreach($user->roles as $role)
                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                            {{ $role->name === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($role->name) }}
                        </span>
                    @endforeach
                </td>
                <td class="px-4 py-3 text-gray-500 text-xs">{{ $user->created_at->format('d M Y') }}</td>
                <td class="px-4 py-3 flex gap-2">
                    <a href="{{ route('users.edit', $user) }}"
                       class="text-xs text-indigo-600 hover:underline">Edit</a>
                    @if($user->id !== auth()->id())
                    <form method="POST" action="{{ route('users.destroy', $user) }}"
                          onsubmit="return confirm('Delete this user?')">
                        @csrf @method('DELETE')
                        <button class="text-xs text-red-500 hover:underline">Delete</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-gray-400">No users found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-4 py-3 border-t border-gray-100">{{ $users->links() }}</div>
</div>
</div>
@endsection
