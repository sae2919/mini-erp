<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Admin user ────────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'admin@erp.test'],
            ['name' => 'Admin', 'password' => Hash::make('password')]
        );

        // ── Categories ────────────────────────────────────────────
        $categoryNames = ['Electronics', 'Clothing', 'Food & Beverage', 'Stationery', 'Hardware'];
        $cats = [];
        foreach ($categoryNames as $name) {
            $cats[] = Category::create(['name' => $name]);
        }

        // ── Suppliers ─────────────────────────────────────────────
        Supplier::create(['name' => 'TechSource Pvt Ltd',  'phone' => '9876543210', 'email' => 'orders@techsource.com']);
        Supplier::create(['name' => 'FabricWorld',          'phone' => '9812345678', 'email' => 'purchase@fabricworld.in']);
        Supplier::create(['name' => 'Metro Distributors',   'phone' => '8800112233', 'email' => 'supply@metro.co.in']);

        // ── Products ──────────────────────────────────────────────
        Product::create(['name' => 'USB-C Cable 2m',   'sku' => 'ELEC-001', 'category_id' => $cats[0]->id, 'price' => 399,  'cost_price' => 180,  'stock_quantity' => 0, 'low_stock_threshold' => 10, 'unit' => 'pcs']);
        Product::create(['name' => 'Wireless Mouse',   'sku' => 'ELEC-002', 'category_id' => $cats[0]->id, 'price' => 799,  'cost_price' => 400,  'stock_quantity' => 0, 'low_stock_threshold' => 10, 'unit' => 'pcs']);
        Product::create(['name' => 'Cotton T-Shirt L', 'sku' => 'CLTH-001', 'category_id' => $cats[1]->id, 'price' => 499,  'cost_price' => 200,  'stock_quantity' => 0, 'low_stock_threshold' => 10, 'unit' => 'pcs']);
        Product::create(['name' => 'Black Pen (Box)',  'sku' => 'STAT-001', 'category_id' => $cats[3]->id, 'price' => 120,  'cost_price' => 60,   'stock_quantity' => 0, 'low_stock_threshold' => 10, 'unit' => 'box']);
        Product::create(['name' => 'A4 Paper Ream',    'sku' => 'STAT-002', 'category_id' => $cats[3]->id, 'price' => 350,  'cost_price' => 200,  'stock_quantity' => 0, 'low_stock_threshold' => 10, 'unit' => 'ream']);

        $this->command->info('Seed complete. Login: admin@erp.test / password');
    }
}