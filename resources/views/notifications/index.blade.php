@extends('layouts.app')

@section('title', 'Notifications')
@section('heading', 'Notifications')

@section('header-actions')
    <form method="POST" action="{{ route('notifications.readAll') }}">
        @csrf

        <button
            type="submit"
            class="inline-flex items-center px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-medium hover:bg-indigo-700 transition">
            Mark all as read
        </button>
    </form>
@endsection

@section('content')

<div class="py-4 max-w-3xl">

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">

        @forelse($notifications as $notif)

            <div class="border-b border-gray-100 last:border-0">

                <form
                    method="POST"
                    action="{{ route('notifications.read', $notif) }}">
                    @csrf

                    <button
                        type="submit"
                        class="w-full text-left flex items-start gap-4 px-5 py-4 hover:bg-gray-50 transition {{ $notif->read_at ? 'opacity-60' : 'bg-indigo-50/40' }}">

                        {{-- Notification Icon --}}
                        <span class="text-2xl mt-0.5 flex-shrink-0">
                            {{ $notif->icon }}
                        </span>

                        {{-- Notification Content --}}
                        <div class="flex-1 min-w-0">

                            <div class="flex items-center justify-between gap-2">

                                <p class="text-sm font-semibold text-gray-800 {{ !$notif->read_at ? 'text-indigo-700' : '' }}">
                                    {{ $notif->title }}
                                </p>

                                @if(!$notif->read_at)
                                    <span class="w-2 h-2 rounded-full bg-indigo-500 flex-shrink-0"></span>
                                @endif

                            </div>

                            <p class="text-sm text-gray-500 mt-0.5">
                                {{ $notif->message }}
                            </p>

                            <p class="text-xs text-gray-400 mt-1">
                                {{ $notif->created_at->diffForHumans() }}
                            </p>

                        </div>
                    </button>
                </form>

            </div>

        @empty

            <div class="px-5 py-12 text-center text-gray-400">

                <p class="text-5xl mb-4">🔔</p>

                <p class="font-semibold text-gray-600">
                    No notifications yet
                </p>

                <p class="text-sm mt-2">
                    Alerts for low stock, payments, returns,
                    and approvals will appear here.
                </p>

            </div>

        @endforelse

    </div>

    {{-- Pagination --}}
    <div class="mt-5">
        {{ $notifications->links() }}
    </div>

</div>

@endsection