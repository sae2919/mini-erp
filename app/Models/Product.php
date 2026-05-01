<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'price',
        'cost_price',
        'stock_quantity',
        'low_stock_threshold',
        'unit',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'stock_quantity'      => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active'           => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function purchaseItems(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold');
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    // ─── Accessors / Helpers ──────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function hasStock(int $qty): bool
    {
        return $this->stock_quantity >= $qty;
    }

    public function profitMargin(): float
    {
        if ($this->price == 0) {
            return 0;
        }
        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }
}
