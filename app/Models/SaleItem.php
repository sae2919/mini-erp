<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'selling_price',
        'cost_price',
    ];

    protected $casts = [
        'quantity'      => 'integer',
        'selling_price' => 'decimal:2',
        'cost_price'    => 'decimal:2',
        'subtotal'      => 'decimal:2',
    ];

    // Sale Relationship
    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    // Product Relationship
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Profit Accessor
    public function getProfitAttribute(): float
    {
        return (
            ($this->selling_price - $this->cost_price)
            * $this->quantity
        );
    }

    // Subtotal Accessor
    public function getSubtotalAttribute(): float
    {
        return $this->selling_price * $this->quantity;
    }
}