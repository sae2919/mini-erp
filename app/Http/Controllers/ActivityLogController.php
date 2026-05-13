<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);

        $perPage = in_array($perPage, [10, 25, 50, 100])
            ? $perPage
            : 10;

        $logs = ActivityLog::with('user')

            // Search Filter
            ->when($request->filled('search'), function ($q) use ($request) {
                $q->where('description', 'like', '%' . $request->search . '%');
            })

            // Action Filter
            ->when($request->filled('action'), function ($q) use ($request) {
                $q->where('action', $request->action);
            })

            // Module Filter
            ->when($request->filled('module'), function ($q) use ($request) {
                $q->where('module', $request->module);
            })

            // User Filter
            ->when($request->filled('user_id'), function ($q) use ($request) {
                $q->where('user_id', $request->user_id);
            })

            // From Date Filter
            ->when($request->filled('from'), function ($q) use ($request) {

                $from = Carbon::createFromFormat(
                    'Y-m-d',
                    $request->from
                )->startOfDay();

                $q->where('created_at', '>=', $from);
            })

            // To Date Filter
            ->when($request->filled('to'), function ($q) use ($request) {

                $to = Carbon::createFromFormat(
                    'Y-m-d',
                    $request->to
                )->endOfDay();

                $q->where('created_at', '<=', $to);
            })

            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        // Dropdown Data
        $actions = ActivityLog::distinct()
            ->pluck('action')
            ->sort()
            ->values();

        $modules = ActivityLog::distinct()
            ->pluck('module')
            ->sort()
            ->values();

        $users = User::orderBy('name')
            ->get(['id', 'name']);

        return view('activity_logs.index', compact(
            'logs',
            'actions',
            'modules',
            'users'
        ));
    }
}