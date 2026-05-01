<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use App\Models\Tax;
use Illuminate\Database\Seeder;

class AdvancedSeeder extends Seeder
{
    public function run(): void
    {
        // ── Tax rates (GST India) ─────────────────────────────────
        $taxes = [
            ['name' => 'GST 0%',   'rate' => 0],
            ['name' => 'GST 5%',   'rate' => 5],
            ['name' => 'GST 12%',  'rate' => 12],
            ['name' => 'GST 18%',  'rate' => 18],
            ['name' => 'GST 28%',  'rate' => 28],
        ];

        foreach ($taxes as $tax) {
            Tax::firstOrCreate(['name' => $tax['name']], $tax);
        }

        // ── Expense Categories ────────────────────────────────────
        $categories = [
            ['name' => 'Rent',           'color' => '#6366f1'],
            ['name' => 'Salaries',        'color' => '#22c55e'],
            ['name' => 'Utilities',       'color' => '#f59e0b'],
            ['name' => 'Marketing',       'color' => '#ec4899'],
            ['name' => 'Transport',       'color' => '#14b8a6'],
            ['name' => 'Office Supplies', 'color' => '#8b5cf6'],
            ['name' => 'Maintenance',     'color' => '#f97316'],
            ['name' => 'Other',           'color' => '#6b7280'],
        ];

        foreach ($categories as $cat) {
            ExpenseCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }

        $this->command->info('✅ Taxes and expense categories seeded.');
    }
}
