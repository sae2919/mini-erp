@extends('layouts.app')

@section('title', 'Activity Log')
@section('heading', 'Activity Log')

@section('content')

<div class="py-4 space-y-4">

    <form method="GET"
      class="bg-white border border-gray-100 rounded-2xl shadow-sm p-5 mb-5">

    <div class="flex flex-wrap items-end gap-4">

        {{-- FROM DATE --}}
        <div class="min-w-[180px] flex-1">
            <label class="block text-sm font-medium text-gray-600 mb-2">
                From
            </label>

            <input
                type="date"
                id="from_date"
                name="from"
                value="{{ request('from') }}"
                class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm">
        </div>

        {{-- TO DATE --}}
        <div class="min-w-[180px] flex-1">
            <label class="block text-sm font-medium text-gray-600 mb-2">
                To
            </label>

            <input
                type="date"
                id="to_date"
                name="to"
                value="{{ request('to') }}"
                min="{{ request('from') }}"
                class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm">
        </div>

        {{-- USER --}}
        <div class="min-w-[180px] flex-1">
            <label class="block text-sm font-medium text-gray-600 mb-2">
                User
            </label>

            <select name="user_id"
                    class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm">

                <option value="">All Users</option>

                @foreach($users as $user)
                    <option value="{{ $user->id }}"
                        {{ request('user_id') == $user->id ? 'selected' : '' }}>
                        {{ $user->name }}
                    </option>
                @endforeach

            </select>
        </div>

        {{-- ACTION --}}
        <div class="min-w-[180px] flex-1">
            <label class="block text-sm font-medium text-gray-600 mb-2">
                Action
            </label>

            <select name="action"
                    class="w-full rounded-xl border-gray-200 focus:border-blue-500 focus:ring-blue-500 text-sm shadow-sm">

                <option value="">All Actions</option>

                @foreach($actions as $action)
                    <option value="{{ $action }}"
                        {{ request('action') == $action ? 'selected' : '' }}>
                        {{ ucfirst($action) }}
                    </option>
                @endforeach

            </select>
        </div>

        {{-- FILTER BUTTON --}}
        <div class="w-auto">
            <button type="submit"
                    class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl shadow-sm transition">
                Filter
            </button>
        </div>

        {{-- CLEAR BUTTON --}}
        <div class="w-auto">
            <a href="{{ route('activity-logs.index') }}"
               class="inline-flex items-center justify-center px-5 py-2.5 border border-gray-200 text-gray-600 text-sm font-medium rounded-xl hover:bg-gray-50 transition">
                Clear
            </a>
        </div>

    </div>

</form>

<script>

    const fromDate = document.getElementById('from_date');
    const toDate   = document.getElementById('to_date');

    fromDate.addEventListener('change', function () {

        toDate.min = this.value;

        if (toDate.value && toDate.value < this.value) {
            toDate.value = this.value;
        }

    });

</script>

{{-- PROFESSIONAL DATE VALIDATION --}}
<script>

    const fromDate = document.getElementById('from_date');
    const toDate   = document.getElementById('to_date');

    fromDate.addEventListener('change', function () {

        // Set minimum selectable TO date
        toDate.min = this.value;

        // Reset invalid TO date automatically
        if (toDate.value && toDate.value < this.value) {
            toDate.value = this.value;
        }

    });

</script>


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
                px-6 py-4
                border-t border-gray-100
                bg-gray-50/30
                flex flex-wrap
                items-center
                justify-between
                gap-4
            "
        >

            {{-- RESULTS INFO --}}
            <div class="text-xs font-bold uppercase tracking-widest text-gray-400">
                Showing <span class="text-gray-700">{{ $logs->firstItem() }}</span> to <span class="text-gray-700">{{ $logs->lastItem() }}</span> of <span class="text-gray-700">{{ $logs->total() }}</span> results
            </div>


            {{-- PAGINATION CONTROLS --}}
            <div class="flex items-center gap-3 flex-wrap">

                {{-- PREVIOUS --}}
                @if($logs->onFirstPage())

                    <span class="w-10 h-10 rounded-xl bg-gray-100 text-gray-300 flex items-center justify-center border border-gray-200 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </span>

                @else

                    <a
                        href="{{ $logs->previousPageUrl() }}"
                        class="
                            w-10 h-10
                            rounded-xl
                            border border-gray-200
                            bg-white
                            hover:bg-indigo-600
                            hover:text-white
                            hover:border-indigo-600
                            flex items-center justify-center
                            text-gray-600
                            transition-all
                            shadow-sm
                            group
                        "
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>

                @endif


                {{-- CURRENT PAGE CHIP --}}
                <div
                    class="
                        h-10 px-5
                        rounded-xl
                        bg-indigo-600
                        text-white
                        flex items-center
                        text-xs font-bold uppercase tracking-wider
                        shadow-md shadow-indigo-100
                    "
                >
                    Page {{ $logs->currentPage() }} / {{ $logs->lastPage() }}
                </div>


                {{-- NEXT --}}
                @if($logs->hasMorePages())

                    <a
                        href="{{ $logs->nextPageUrl() }}"
                        class="
                            w-10 h-10
                            rounded-xl
                            border border-gray-200
                            bg-white
                            hover:bg-indigo-600
                            hover:text-white
                            hover:border-indigo-600
                            flex items-center justify-center
                            text-gray-600
                            transition-all
                            shadow-sm
                            group
                        "
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>

                @else

                    <span class="w-10 h-10 rounded-xl bg-gray-100 text-gray-300 flex items-center justify-center border border-gray-200 cursor-not-allowed">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
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
                                <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                            @endforeach
                        @else
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endif
                    @endforeach

                    <input
                        type="number"
                        name="page"
                        min="1"
                        max="{{ $logs->lastPage() }}"
                        placeholder="Page"
                        class="
                            w-20 h-10
                            border border-gray-200
                            rounded-xl
                            px-3
                            text-xs font-medium
                            outline-none
                            focus:ring-2
                            focus:ring-indigo-100
                            focus:border-indigo-400
                            transition-all
                        "
                    >

                    <button
                        type="submit"
                        class="
                            h-10 px-4
                            rounded-xl
                            bg-gray-900
                            hover:bg-black
                            text-white
                            text-xs
                            font-bold uppercase tracking-wider
                            transition-all
                            active:scale-95
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