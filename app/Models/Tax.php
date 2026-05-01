<?php namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tax extends Model {
    protected $fillable = ['name', 'rate', 'is_active'];
    protected $casts    = ['rate' => 'decimal:2', 'is_active' => 'boolean'];
    public function products(): HasMany { return $this->hasMany(Product::class); }
    public static function active() { return static::where('is_active', true)->orderBy('rate')->get(); }
}
