<?php

use App\Models\StockMovement;

if (!function_exists('logStock')) {
    function logStock($productId, $type, $qty, $refType = null, $refId = null, $note = null)
    {
        StockMovement::create([
            'product_id' => $productId,
            'type' => $type,
            'quantity' => $qty,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'note' => $note,
            'user_id' => auth()->id(),
        ]);
    }
}