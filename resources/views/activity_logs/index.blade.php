@extends('layouts.app')

@section('title', 'Activity Log')
@section('heading', 'Activity Log')

@section('content')

<div class="py-4 space-y-4">

    {{-- FILTERS --}}
    <form
        method="GET"
        class="bg-white rounded-xl border border-gray-100 shadow-sm p-4 flex flex-wrap gap-3 items-end"
    >

        <div>
            <label class="block text-xs text-gray-500 mb-1">
                From
            </label>

            <input
                type="date"
                name="from"
                value="{{ request('from') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">
                To
            </label>

            <input
                type="date"
                name="to"
                value="{{ request('to') }}"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">
                User
            </label>

            <select
                name="user_id"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >

                <option value="">
                    All Users
                </option>

                @foreach($users as $user)

                    <option
                        value="{{ $user->id }}"
                        {{ request('user_id') == $user->id ? 'selected' : '' }}
                    >
                        {{ $user->name }}
                    </option>

                @endforeach

            </select>
        </div>

        <div>
            <label class="block text-xs text-gray-500 mb-1">
                Action
            </label>

            <select
                name="action"
                class="border border-gray-300 rounded-lg px-3 py-2 text-sm"
            >

                <option value="">
                    All Actions
                </option>

                @foreach($actions as $action)

                    <option
                        value="{{ $action }}"
                        {{ request('action') == $action ? 'selected' : '' }}
                    >
                        {{ $action }}
                    </option>

                @endforeach

            </select>
        </div>

        {{-- FILTER --}}
        <button
            class="
                px-4 py-2
                bg-indigo-600
                text-white
                rounded-lg
                text-sm
                hover:bg-indigo-700
                transition
            "
        >
            Filter
        </button>

        {{-- CLEAR --}}
        @if(request()->hasAny(['from','to','user_id','action']))

            <a
                href="{{ route('activity-logs.index') }}"
                class="text-sm text-gray-500 hover:underline"
            >
                Clear
            </a>

        @endif

    </form>


    {{-- TABLE --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">

        <div class="overflow-x-auto">

            <table class="w-full text-sm">

                <thead class="bg-gray-50 border-b border-gray-100">

                    <tr class="text-left text-gray-500 font-medium">

                        <th class="px-4 py-3">
                            Time
                        </th>

                        <th class="px-4 py-3">
                            User
                        </th>

                        <th class="px-4 py-3">
                            Action
                        </th>

                        <th class="px-4 py-3">
                            Description
                        </th>

                        <th class="px-4 py-3">
                            IP
                        </th>

                    </tr>

                </thead>

                <tbody class="divide-y divide-gray-50">

                    @forelse($logs as $log)

                        <tr class="hover:bg-gray-50 transition">

                            {{-- TIME --}}
                            <td class="px-4 py-3 text-xs text-gray-500 whitespace-nowrap">

                                {{ $log->created_at->format('d M Y') }}

                                <br>

                                <span class="text-gray-400">

                                    {{ $log->created_at->format('h:i A') }}

                                </span>

                            </td>

                            {{-- USER --}}
                            <td class="px-4 py-3">

                                <span class="font-medium text-gray-800">

                                    {{ $log->user?->name ?? 'System' }}

                                </span>

                            </td>

                            {{-- ACTION --}}
                            <td class="px-4 py-3">

                                <span
                                    class="
                                        px-2 py-0.5
                                        text-xs
                                        font-semibold
                                        rounded-full
                                        {{ \App\Models\ActivityLog::actionColor($log->action) }}
                                    "
                                >
                                    {{ $log->action }}
                                </span>

                            </td>

                            {{-- DESCRIPTION --}}
                            <td class="px-4 py-3 text-gray-700 max-w-xl">

                                {{ $log->description }}

                            </td>

                            {{-- IP --}}
                            <td class="px-4 py-3 text-xs text-gray-400 font-mono whitespace-nowrap">

                                {{ $log->ip_address }}

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="5"
                                class="px-4 py-10 text-center text-gray-400"
                            >
                                No activity logged yet.
                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>


        {{-- PROFESSIONAL PAGINATION --}}
        @if ($logs->hasPages())

        <div
            class="
                px-5 py-4
                border-t border-gray-100
                flex flex-wrap
                items-center
                justify-between
                gap-4
            "
        >

            {{-- RESULTS --}}
            <div class="text-sm text-gray-500">

                Showing

                <span class="font-semibold text-gray-700">
                    {{ $logs->firstItem() }}
                </span>

                to

                <span class="font-semibold text-gray-700">
                    {{ $logs->lastItem() }}
                </span>

                of

                <span class="font-semibold text-gray-700">
                    {{ $logs->total() }}
                </span>

                results

            </div>


            {{-- PAGINATION --}}
            <div class="flex items-center gap-3 flex-wrap">

                {{-- PREVIOUS --}}
                @if($logs->onFirstPage())

                    <span
                        class="
                            w-12 h-12
                            rounded-lg
                            bg-gray-100
                            text-gray-400
                            text-3xl
                            font-bold
                            flex items-center justify-center
                        "
                    >
                        ‹
                    </span>

                @else

                    <a
                        href="{{ $logs->previousPageUrl() }}"
                        class="
                            w-12 h-12
                            rounded-lg
                            border border-gray-200
                            bg-white
                            hover:bg-gray-50
                            flex items-center justify-center
                            text-gray-700
                            text-3xl
                            font-extrabold
                            transition
                        "
                    >
                        ‹
                    </a>

                @endif


                {{-- PAGE INFO --}}
                <div
                    class="
                        h-12 px-5
                        rounded-lg
                        bg-indigo-600
                        text-white
                        flex items-center
                        text-sm font-semibold
                    "
                >

                    Page {{ $logs->currentPage() }}

                    of

                    {{ $logs->lastPage() }}

                </div>


                {{-- NEXT --}}
                @if($logs->hasMorePages())

                    <a
                        href="{{ $logs->nextPageUrl() }}"
                        class="
                            w-12 h-12
                            rounded-lg
                            border border-gray-200
                            bg-white
                            hover:bg-gray-50
                            flex items-center justify-center
                            text-gray-700
                            text-3xl
                            font-extrabold
                            transition
                        "
                    >
                        ›

                    </a>

                @else

                    <span
                        class="
                            w-12 h-12
                            rounded-lg
                            bg-gray-100
                            text-gray-400
                            text-3xl
                            font-bold
                            flex items-center justify-center
                        "
                    >
                        ›

                    </span>

                @endif


                {{-- JUMP TO PAGE --}}
                <form
                    method="GET"
                    action="{{ url()->current() }}"
                    class="flex items-center gap-2 ml-2"
                >

                    @foreach(request()->except('page') as $key => $value)

                        @if(is_array($value))

                            @foreach($value as $v)

                                <input
                                    type="hidden"
                                    name="{{ $key }}[]"
                                    value="{{ $v }}"
                                >

                            @endforeach

                        @else

                            <input
                                type="hidden"
                                name="{{ $key }}"
                                value="{{ $value }}"
                            >

                        @endif

                    @endforeach

                    <input
                        type="number"
                        name="page"
                        min="1"
                        max="{{ $logs->lastPage() }}"
                        placeholder="Page"
                        class="
                            w-24 h-12
                            border border-gray-300
                            rounded-lg
                            px-3
                            text-sm
                            outline-none
                            focus:ring-2
                            focus:ring-indigo-200
                        "
                    >

                    <button
                        type="submit"
                        class="
                            h-12 px-5
                            rounded-lg
                            bg-gray-900
                            hover:bg-black
                            text-white
                            text-sm
                            font-semibold
                            transition
                        "
                    >
                        Go
                    </button>

                </form>

            </div>

        </div>

        @endif

    </div>

</div>

@endsection