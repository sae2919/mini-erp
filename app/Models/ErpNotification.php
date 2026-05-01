<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class ErpNotification extends Model
{
    protected $table = 'erp_notifications';

    protected $fillable = [
        'user_id', 'type', 'title', 'message', 'icon', 'color', 'url', 'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where(fn($q) =>
            $q->where('user_id', $userId)->orWhereNull('user_id')
        );
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public static function notify(string $type, string $title, string $message, array $options = []): void
    {
        static::create([
            'user_id' => $options['user_id'] ?? null,
            'type'    => $type,
            'title'   => $title,
            'message' => $message,
            'icon'    => $options['icon']  ?? '🔔',
            'color'   => $options['color'] ?? 'indigo',
            'url'     => $options['url']   ?? null,
        ]);
    }
}
