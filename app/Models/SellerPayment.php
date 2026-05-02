<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerPayment extends Model
{
    protected $fillable = ['seller_id','dispatch_order_id','user_id','amount','method','reference','paid_at','notes'];
    protected $casts    = ['amount'=>'decimal:2','paid_at'=>'date'];

    public function seller(): BelongsTo        { return $this->belongsTo(Seller::class); }
    public function dispatchOrder(): BelongsTo { return $this->belongsTo(DispatchOrder::class); }
    public function user(): BelongsTo          { return $this->belongsTo(User::class); }

    public static function methods(): array {
        return ['cash'=>'💵 Cash','upi'=>'📱 UPI','bank_transfer'=>'🏦 Bank Transfer','cheque'=>'📝 Cheque'];
    }
}
