<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Quotation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'reference', 'customer_name', 'customer_email', 'customer_phone',
        'quotation_date', 'valid_until', 'status', 'subtotal', 'tax_amount',
        'discount_amount', 'total_amount', 'notes', 'terms', 'converted_to_sale_id',
    ];

    protected $casts = [
        'quotation_date'  => 'date',
        'valid_until'     => 'date',
        'subtotal'        => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function customer(): BelongsTo   { return $this->belongsTo(Customer::class); }
    public function items(): HasMany        { return $this->hasMany(QuotationItem::class); }
    public function convertedSale(): BelongsTo { return $this->belongsTo(Sale::class, 'converted_to_sale_id'); }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('quotation_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('quotation_date', '<=', $to));
    }

    public static function generateReference(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'QUO-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }

    public function isExpired(): bool
    {
        return $this->valid_until->isPast() && !in_array($this->status, ['accepted', 'converted', 'rejected']);
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'draft'     => 'bg-gray-100 text-gray-600',
            'sent'      => 'bg-blue-100 text-blue-700',
            'accepted'  => 'bg-green-100 text-green-700',
            'rejected'  => 'bg-red-100 text-red-700',
            'expired'   => 'bg-orange-100 text-orange-700',
            'converted' => 'bg-indigo-100 text-indigo-700',
            default     => 'bg-gray-100 text-gray-600',
        };
    }
}
