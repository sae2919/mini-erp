<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Production extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id','reference','production_date','total_cost','notes','status'];
    protected $casts    = ['production_date'=>'date','total_cost'=>'decimal:2'];

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany  { return $this->hasMany(ProductionItem::class); }

    public static function generateReference(): string
    {
        $last = static::withTrashed()->latest('id')->first();
        $next = $last ? $last->id + 1 : 1;
        return 'PRD-' . str_pad($next, 6, '0', STR_PAD_LEFT);
    }
}
