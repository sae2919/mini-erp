<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = [
        'sale_id', 'user_id', 'amount', 'method', 'reference', 'paid_at', 'notes',
    ];

    protected $casts = [
        'amount'  => 'decimal:2',
        'paid_at' => 'date',
    ];

    public function sale(): BelongsTo { return $this->belongsTo(Sale::class); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public static function methods(): array
    {
        return [
            'cash'          => '💵 Cash',
            'upi'           => '📱 UPI',
            'card'          => '💳 Card',
            'bank_transfer' => '🏦 Bank Transfer',
            'cheque'        => '📝 Cheque',
        ];
    }
}
