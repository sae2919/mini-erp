<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchaseReturn extends Model
{
    protected $fillable = [
        'purchase_id', 'supplier_id', 'user_id', 'reference',
        'return_date', 'reason', 'total_amount', 'status', 'notes',
    ];

    protected $casts = [
        'return_date'  => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function purchase(): BelongsTo { return $this->belongsTo(Purchase::class); }
    public function supplier(): BelongsTo { return $this->belongsTo(Supplier::class); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }
    public function items(): HasMany      { return $this->hasMany(PurchaseReturnItem::class); }

    public static function generateReference(): string
    {
        $last = static::latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'PRR-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public static function reasons(): array
    {
        return [
            'damaged'        => 'Damaged / Defective',
            'wrong_item'     => 'Wrong Item Received',
            'excess_qty'     => 'Excess Quantity',
            'quality'        => 'Quality Issue',
            'price_dispute'  => 'Price Dispute',
            'expired'        => 'Expired / Near Expiry',
            'other'          => 'Other',
        ];
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'approved' => 'bg-green-100 text-green-700',
            'pending'  => 'bg-yellow-100 text-yellow-700',
            'rejected' => 'bg-red-100 text-red-700',
            default    => 'bg-gray-100 text-gray-600',
        };
    }
}
