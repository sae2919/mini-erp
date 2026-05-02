<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SellerSale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_id','reference','customer_name','customer_phone',
        'sale_date','total_amount','commission_amount','company_amount','payment_status','notes',
    ];
    protected $casts = ['sale_date'=>'date','total_amount'=>'decimal:2','commission_amount'=>'decimal:2','company_amount'=>'decimal:2'];

    public function seller(): BelongsTo  { return $this->belongsTo(Seller::class); }
    public function items(): HasMany     { return $this->hasMany(SellerSaleItem::class); }
    public function commission(): HasOne { return $this->hasOne(Commission::class); }

    public static function generateReference(int $sellerId): string
    {
        $last = static::withTrashed()->where('seller_id', $sellerId)->latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'SLS-' . $sellerId . '-' . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    public function paymentStatusColor(): string {
        return match($this->payment_status) {
            'paid'    => 'bg-green-100 text-green-700',
            'partial' => 'bg-yellow-100 text-yellow-700',
            default   => 'bg-red-100 text-red-700',
        };
    }
}
