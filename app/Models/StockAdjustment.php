<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StockAdjustment extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'reason',
        'notes',
    ];

    protected $casts = [
        'quantity'        => 'integer',
        'quantity_before' => 'integer',
        'quantity_after'  => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('created_at', '<=', $to));
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isAdd(): bool
    {
        return $this->type === 'add';
    }

    public static function reasons(): array
    {
        return [
            'damaged'       => 'Damaged Goods',
            'expired'       => 'Expired / Spoiled',
            'lost'          => 'Lost / Stolen',
            'found'         => 'Stock Found',
            'correction'    => 'Count Correction',
            'write_off'     => 'Write-Off',
            'return'        => 'Customer Return',
            'other'         => 'Other',
        ];
    }
}
