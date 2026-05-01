<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Sale extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
    'reference',
    'customer_id',
    'customer_name',
    'sale_date',
    'total_amount',
    'subtotal_amount',
    'tax_amount',
    'discount_amount',
    'notes',
    'order_type',
    'status',
    'payment_status',        // ← make sure this is here
    'shipping_name',
    'shipping_phone',
    'shipping_email',
    'shipping_address',
];

    protected $casts = [
        'sale_date'    => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('sale_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('sale_date', '<=', $to));
    }

    public static function generateReference(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'INV-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function totalProfit(): float
    {
        return $this->items->sum(fn($item) =>
            ($item->selling_price - $item->cost_price) * $item->quantity
        );
    }

    /**
     * Display name — use linked customer name if available, else manual name, else Walk-in
     */
    public function getCustomerDisplayAttribute(): string
    {
        return $this->customer?->name ?? $this->customer_name ?? 'Walk-in';
    }
    public function payments(): HasMany
{
    return $this->hasMany(\App\Models\Payment::class);
}
public function statusLabel(): string
{
    return match($this->status) {
        'pending'    => '🕐 Pending',
        'confirmed'  => '✅ Confirmed',
        'processing' => '⚙️ Processing',
        'shipped'    => '🚚 Shipped',
        'delivered'  => '📦 Delivered',
        'cancelled'  => '❌ Cancelled',
        'completed'  => '✅ Completed',
        default      => ucfirst($this->status ?? ''),
    };
}

public function statusColor(): string
{
    return match($this->status) {
        'pending'    => 'bg-yellow-100 text-yellow-700',
        'confirmed'  => 'bg-blue-100 text-blue-700',
        'processing' => 'bg-indigo-100 text-indigo-700',
        'shipped'    => 'bg-purple-100 text-purple-700',
        'delivered'  => 'bg-green-100 text-green-700',
        'cancelled'  => 'bg-red-100 text-red-700',
        'completed'  => 'bg-green-100 text-green-700',
        default      => 'bg-gray-100 text-gray-700',
    };
}

public function nextStatuses(): array
{
    return match($this->status) {
        'pending'    => ['confirmed', 'cancelled'],
        'confirmed'  => ['processing', 'cancelled'],
        'processing' => ['shipped', 'cancelled'],
        'shipped'    => ['delivered'],
        default      => [],
    };
}

public static function allStatuses(): array
{
    return ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'completed'];
}
}
