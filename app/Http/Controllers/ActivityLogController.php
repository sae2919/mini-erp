<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // INDEX
    // ─────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        $perPage = in_array($perPage, [10, 25, 50, 100]) ? $perPage : 10;

        $logs = ActivityLog::with('user')
            ->when($request->filled('search'), fn($q) =>
                $q->where('description', 'like', '%' . $request->search . '%')
            )
            ->when($request->filled('action'), fn($q) =>
                $q->where('action', $request->action)
            )
            ->when($request->filled('user_id'), fn($q) =>
                $q->where('user_id', $request->user_id)
            )
            ->when($request->filled('date_from'), fn($q) =>
                $q->whereDate('created_at', '>=', $request->date_from)
            )
            ->when($request->filled('date_to'), fn($q) =>
                $q->whereDate('created_at', '<=', $request->date_to)
            )
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

       $actions = ActivityLog::distinct()->pluck('action')->sort()->values();
$modules = ActivityLog::distinct()->pluck('module')->sort()->values();  // ← ADD
$users   = \App\Models\User::orderBy('name')->get(['id', 'name']);

return view('activity_logs.index', compact('logs', 'actions', 'modules', 'users')); // ← ADD modules

    }
}
