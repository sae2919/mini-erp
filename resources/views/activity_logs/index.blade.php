@extends('layouts.app')
@section('title', 'Activity Log')
@section('heading', 'Activity Log')

@section('content')
<div class="py-4 space-y-4">

    <form method="GET" class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-gray-500 mb-1">From</label>
            <input type="date" name="from" value="{{ request('from') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">To</label>
            <input type="date" name="to" value="{{ request('to') }}"
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">User</label>
            <select name="user_id" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Users</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Module</label>
            <select name="module" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Modules</option>
                @foreach($modules as $module)
                    <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>{{ $module }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-gray-500 mb-1">Action</label>
            <select name="action" class="border border-gray-300 rounded-lg px-3 py-2 text-sm">
                <option value="">All Actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>{{ $action }}</option>
                @endforeach
            </select>
        </div>
        <button class="px-4 py-2 bg-indigo-600 text-white rounded-lg text-sm hover:bg-indigo-700 transition">Filter</button>
        @if(request()->hasAny(['from','to','user_id','module','action']))
            <a href="{{ route('activity-logs.index') }}" class="text-sm text-gray-500 hover:underline">Clear</a>
        @endif
    </form>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr class="text-left text-gray-500 font-medium">
                    <th class="px-4 py-3">Time</th>
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Action</th>
                    <th class="px-4 py-3">Module</th>
                    <th class="px-4 py-3">Description</th>
                    <th class="px-4 py-3">IP</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($logs as $log)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">
                        {{ $log->created_at->format('d M Y') }}<br>
                        <span class="text-gray-400">{{ $log->created_at->format('h:i A') }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium text-gray-800">{{ $log->user?->name ?? 'System' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ \App\Models\ActivityLog::actionColor($log->action) }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-600 text-xs font-mono">{{ $log->module }}</td>
                    <td class="px-4 py-3 text-gray-700 max-w-xs">{{ $log->description }}</td>
                    <td class="px-4 py-3 text-xs text-gray-400 font-mono">{{ $log->ip_address }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No activity logged yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100">{{ $logs->links() }}</div>
    </div>
</div>
@endsection
