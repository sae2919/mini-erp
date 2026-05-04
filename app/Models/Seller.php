<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Seller extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id','name','phone','email','address',
        'region','credit_limit','balance_due','is_active','notes',
        'commission_rate',
    ];

    protected $casts = [
        'credit_limit'    => 'decimal:2',
        'balance_due'     => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'is_active'       => 'boolean',
    ];

    public function user(): BelongsTo         { return $this->belongsTo(User::class); }
    public function stocks(): HasMany         { return $this->hasMany(SellerStock::class); }
    public function dispatchOrders(): HasMany { return $this->hasMany(DispatchOrder::class); }
    public function sales(): HasMany          { return $this->hasMany(SellerSale::class); }
    public function payments(): HasMany       { return $this->hasMany(SellerPayment::class); }
    public function commissions(): HasMany    { return $this->hasMany(Commission::class); }

    public function scopeActive(Builder $q): Builder { return $q->where('is_active', true); }

    public function stockOf(int $productId): int
    {
        return $this->stocks()->where('product_id', $productId)->value('quantity') ?? 0;
    }
}