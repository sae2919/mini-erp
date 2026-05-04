<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;

class ActivityObserver
{
    public function created(Model $model)
{
    $module = class_basename($model);

    $description = match ($module) {
        'Customer' => "Customer {$model->name} created",
        'Sale' => "Sale {$model->invoice_no} — ₹{$model->total}",
        'SellerSale' => "Seller sale {$model->invoice_no} — ₹{$model->total}",
        'Product' => "Product {$model->name} created",
        default => "$module created (ID: {$model->id})"
    };

    logActivity('created', $module, $description, $model->id);
}

    public function updated(Model $model)
{
    $changes = $model->getChanges();
    $original = $model->getOriginal();

    $fields = [];

    foreach ($changes as $key => $value) {
        if (isset($original[$key]) && $original[$key] != $value) {
            $fields[] = "$key: {$original[$key]} → {$value}";
        }
    }

    $description = class_basename($model) . " updated: " . implode(', ', $fields);

    logActivity(
        'updated',
        class_basename($model),
        $description,
        $model->id
    );
}

    public function deleted(Model $model)
    {
        logActivity(
            'deleted',
            class_basename($model),
            class_basename($model) . " deleted (ID: {$model->id})",
            $model->id
        );
    }
}