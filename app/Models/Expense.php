<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'expense_category_id', 'user_id', 'title', 'amount',
        'expense_date', 'payment_method', 'reference', 'receipt', 'notes',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function category(): BelongsTo { return $this->belongsTo(ExpenseCategory::class, 'expense_category_id'); }
    public function user(): BelongsTo     { return $this->belongsTo(User::class); }

    public function scopeDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn($q) => $q->whereDate('expense_date', '>=', $from))
            ->when($to,   fn($q) => $q->whereDate('expense_date', '<=', $to));
    }
}
