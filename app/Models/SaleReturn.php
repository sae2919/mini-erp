<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaleReturn extends Model
{
    protected $fillable = [
        'sale_id', 'user_id', 'reference', 'return_date',
        'reason', 'total_amount', 'status', 'notes',
    ];

    protected $casts = [
        'return_date'  => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function sale(): BelongsTo  { return $this->belongsTo(Sale::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
    public function items(): HasMany   { return $this->hasMany(SaleReturnItem::class); }

    public static function generateReference(): string
    {
        $last = static::latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'RET-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public static function reasons(): array
    {
        return [
            'damaged'      => 'Damaged / Defective',
            'wrong_item'   => 'Wrong Item Delivered',
            'not_needed'   => 'No Longer Needed',
            'quality'      => 'Quality Issue',
            'overcharged'  => 'Overcharged',
            'other'        => 'Other',
        ];
    }
}
