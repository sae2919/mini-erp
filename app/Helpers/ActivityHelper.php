<?php

use App\Models\ActivityLog;

if (!function_exists('logActivity')) {

    function logActivity($action, $module, $description, $subject_id = null)
    {
        try {
            ActivityLog::create([
                'user_id' => auth()->id() ?? 1,
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'subject_id' => $subject_id,
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);
        } catch (\Exception $e) {
            // Optional: log error if needed
            \Log::error('Activity Log Failed: ' . $e->getMessage());
        }
    }
}