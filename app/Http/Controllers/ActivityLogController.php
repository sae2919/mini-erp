<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->dateRange($request->from, $request->to)
            ->when($request->user_id,  fn($q) => $q->where('user_id', $request->user_id))
            ->when($request->module,   fn($q) => $q->where('module', $request->module))
            ->when($request->action,   fn($q) => $q->where('action', $request->action))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $users   = User::orderBy('name')->get();
        $modules = ActivityLog::distinct()->pluck('module')->sort()->values();
        $actions = ActivityLog::distinct()->pluck('action')->sort()->values();

        return view('activity_logs.index', compact('logs', 'users', 'modules', 'actions'));
    }
}
