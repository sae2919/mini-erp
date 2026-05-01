<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleReturnItem extends Model {
    protected $fillable = ['sale_return_id','product_id','quantity','price','subtotal'];
    protected $casts    = ['price'=>'decimal:2','subtotal'=>'decimal:2'];
    public function saleReturn(): BelongsTo { return $this->belongsTo(SaleReturn::class); }
    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
}
