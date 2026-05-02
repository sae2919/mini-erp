<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Commission extends Model
{
    protected $fillable = ['seller_id','seller_sale_id','amount','status','paid_at'];
    protected $casts    = ['amount'=>'decimal:2','paid_at'=>'date'];

    public function seller(): BelongsTo     { return $this->belongsTo(Seller::class); }
    public function sellerSale(): BelongsTo { return $this->belongsTo(SellerSale::class); }
}
