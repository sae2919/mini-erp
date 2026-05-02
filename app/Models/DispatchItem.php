<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispatchItem extends Model {
    protected $fillable = ['dispatch_order_id','product_id','quantity','dispatch_price','subtotal'];
    protected $casts    = ['dispatch_price'=>'decimal:2','subtotal'=>'decimal:2'];
    public function dispatchOrder(): BelongsTo { return $this->belongsTo(DispatchOrder::class); }
    public function product(): BelongsTo       { return $this->belongsTo(Product::class); }
}
