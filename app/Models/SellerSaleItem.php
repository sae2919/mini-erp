<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerSaleItem extends Model {
    protected $fillable = ['seller_sale_id','product_id','quantity','selling_price','dispatch_price','commission_rate','commission_amount','subtotal'];
    protected $casts    = ['selling_price'=>'decimal:2','dispatch_price'=>'decimal:2','commission_rate'=>'decimal:2','commission_amount'=>'decimal:2','subtotal'=>'decimal:2'];
    public function sellerSale(): BelongsTo { return $this->belongsTo(SellerSale::class); }
    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
}
