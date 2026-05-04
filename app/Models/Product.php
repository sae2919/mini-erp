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
        'description_long',
        'image',
        'is_featured',
        'is_available_online',
        'is_active',
        // ── Manufacturer fields ────────────────────────────────────────────
        'production_cost',
        'dispatch_price',
        'mrp',
        'commission_rate',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'production_cost'     => 'decimal:2',
        'dispatch_price'      => 'decimal:2',
        'mrp'                 => 'decimal:2',
        'commission_rate'     => 'decimal:2',
        'stock_quantity'      => 'integer',
        'low_stock_threshold' => 'integer',
        'is_active'           => 'boolean',
        'is_featured'         => 'boolean',
        'is_available_online' => 'boolean',
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

    public function productionItems(): HasMany
    {
        return $this->hasMany(ProductionItem::class);
    }

    public function dispatchItems(): HasMany
    {
        return $this->hasMany(DispatchItem::class);
    }

    public function sellerSaleItems(): HasMany
    {
        return $this->hasMany(SellerSaleItem::class);
    }

    public function sellerStocks(): HasMany
    {
        return $this->hasMany(SellerStock::class);
    }

    // FIX: missing relationship — ProductController::stockHistory() queries
    // StockMovement by product_id. Without this the controller works but
    // the model has no typed relationship, making eager loading impossible.
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class)->latest();
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
              ->orWhere('sku',  'like', "%{$term}%");
        });
    }

    public function scopeOnline(Builder $query): Builder
    {
        return $query->where('is_available_online', true)
                     ->where('is_active', true)
                     ->where('stock_quantity', '>', 0);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function hasStock(int $qty): bool
    {
        return $this->stock_quantity >= $qty;
    }

    /** Old sales system: margin based on price vs cost_price */
    public function profitMargin(): float
    {
        if ($this->price == 0) return 0;
        return round((($this->price - $this->cost_price) / $this->price) * 100, 2);
    }

    /** Manufacturer system: commission earned per unit by seller */
    public function commissionPerUnit(): float
    {
        return round(($this->dispatch_price ?? 0) * (($this->commission_rate ?? 0) / 100), 2);
    }

    /** Manufacturer system: margin per unit (dispatch price minus production cost) */
    public function manufacturerMargin(): float
    {
        return ($this->dispatch_price ?? 0) - ($this->production_cost ?? 0);
    }

    /** Product image URL with placeholder fallback */
    public function imageUrl(): string
    {
        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }
        return 'https://placehold.co/400x400/f3f4f6/9ca3af?text=' . urlencode($this->name);
    }
}