@extends('layouts.app')
@section('title', 'Notifications')
@section('heading', 'Notifications')

@section('header-actions')
    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf
        <button class="text-sm text-indigo-600 hover:underline">Mark all as read</button>
    </form>
@endsection

@section('content')
<div class="py-4 max-w-3xl">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm divide-y divide-gray-50">
        @forelse($notifications as $notif)
        <a href="{{ route('notifications.read', $notif) }}"
           class="flex items-start gap-4 px-5 py-4 hover:bg-gray-50 transition {{ $notif->read_at ? 'opacity-60' : '' }}">
            <span class="text-2xl mt-0.5 flex-shrink-0">{{ $notif->icon }}</span>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-800 {{ !$notif->read_at ? 'text-indigo-700' : '' }}">
                        {{ $notif->title }}
                    </p>
                    @if(!$notif->read_at)
                        <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">{{ $notif->message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
            </div>
        </a>
        @empty
        <div class="px-5 py-12 text-center text-gray-400">
            <p class="text-4xl mb-3">🔔</p>
            <p class="font-medium">No notifications yet</p>
            <p class="text-sm mt-1">Alerts for low stock, payments, and returns will appear here.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
