<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductionItem extends Model
{
    protected $fillable = ['production_id','product_id','quantity','unit_cost','subtotal'];
    protected $casts    = ['unit_cost'=>'decimal:2','subtotal'=>'decimal:2'];

    public function production(): BelongsTo { return $this->belongsTo(Production::class); }
    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
}
