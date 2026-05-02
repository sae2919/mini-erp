<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockRequest extends Model
{
    protected $fillable = [
        'seller_id','product_id','quantity','status','note'
    ];

    public function seller() {
        return $this->belongsTo(Seller::class);
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
