<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'color'];

    public function expenses(): HasMany { return $this->hasMany(Expense::class); }
}
