<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    /**
     * Log an action against a model.
     */
    public static function log(
        string $action,
        string $module,
        string $description,
        ?int   $subjectId  = null,
        ?array $properties = null
    ): void {
        try {
            ActivityLog::create([
                'user_id'     => auth()->id(),
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'subject_id'  => $subjectId,
                'ip_address'  => request()->ip(),
                'properties'  => $properties,
            ]);
        } catch (\Throwable) {
            // Never let logging crash the app
        }
    }

    public static function created(Model $model, string $description): void
    {
        self::log('created', class_basename($model), $description, $model->getKey());
    }

    public static function updated(Model $model, string $description): void
    {
        self::log('updated', class_basename($model), $description, $model->getKey());
    }

    public static function deleted(Model $model, string $description): void
    {
        self::log('deleted', class_basename($model), $description, $model->getKey());
    }

    public static function exported(string $module, string $description): void
    {
        self::log('exported', $module, $description);
    }
}
