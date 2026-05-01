<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ActivityLog extends Model
{
    public $timestamps = true;
    const UPDATED_AT = null; // Only created_at needed

    protected $fillable = [
        'user_id',
        'action',
        'module',
        'description',
        'subject_id',
        'ip_address',
        'properties',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to));
    }

    public static function actionColor(string $action): string
    {
        return match(true) {
            str_contains($action, 'created') => 'bg-green-100 text-green-700',
            str_contains($action, 'deleted') => 'bg-red-100 text-red-700',
            str_contains($action, 'updated') => 'bg-blue-100 text-blue-700',
            str_contains($action, 'export')  => 'bg-purple-100 text-purple-700',
            str_contains($action, 'login')   => 'bg-gray-100 text-gray-600',
            default                          => 'bg-yellow-100 text-yellow-700',
        };
    }
}
