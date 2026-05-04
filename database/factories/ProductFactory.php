<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'category_id'         => Category::factory(),
            'name'                => $this->faker->words(3, true),
            'sku'                 => strtoupper($this->faker->unique()->bothify('SKU-####')),
            'price'               => $this->faker->randomFloat(2, 50, 500),
            'cost_price'          => $this->faker->randomFloat(2, 20, 200),
            'stock_quantity'      => 0,
            'low_stock_threshold' => 5,
            'unit'                => 'pcs',
            'is_active'           => true,
            'is_available_online' => false,
            'is_featured'         => false,
        ];
    }
}