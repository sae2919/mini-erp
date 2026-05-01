<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'supplier_id',
        'reference',
        'purchase_date',
        'total_amount',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'total_amount'  => 'decimal:2',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('purchase_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('purchase_date', '<=', $to));
    }

    // ─── Reference generator ──────────────────────────────────────

    public static function generateReference(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'PO-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
