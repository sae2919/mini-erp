<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DispatchOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_id','user_id','reference','dispatch_date',
        'total_amount','paid_amount','payment_status','status','notes',
    ];
    protected $casts = ['dispatch_date'=>'date','total_amount'=>'decimal:2','paid_amount'=>'decimal:2'];

    public function seller(): BelongsTo { return $this->belongsTo(Seller::class); }
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function items(): HasMany    { return $this->hasMany(DispatchItem::class); }
    public function payments(): HasMany { return $this->hasMany(SellerPayment::class); }

    public static function generateReference(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'DSP-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function balanceDue(): float { return max(0, $this->total_amount - $this->paid_amount); }

    public function statusColor(): string {
        return match($this->status) {
            'dispatched' => 'bg-blue-100 text-blue-700',
            'received'   => 'bg-green-100 text-green-700',
            'pending'    => 'bg-yellow-100 text-yellow-700',
            'cancelled'  => 'bg-red-100 text-red-700',
            default      => 'bg-gray-100 text-gray-600',
        };
    }

    public function paymentStatusColor(): string {
        return match($this->payment_status) {
            'paid'    => 'bg-green-100 text-green-700',
            'partial' => 'bg-yellow-100 text-yellow-700',
            default   => 'bg-red-100 text-red-700',
        };
    }
}
