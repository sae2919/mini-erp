<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Create all 5 roles ────────────────────────────────────
        Role::firstOrCreate(['name' => 'admin']);
        Role::firstOrCreate(['name' => 'inventory_manager']);
        Role::firstOrCreate(['name' => 'sales_executive']);
        Role::firstOrCreate(['name' => 'viewer']);
        Role::firstOrCreate(['name' => 'customer']);

        // ── Assign roles to demo users ────────────────────────────
        $users = [
            ['email' => 'admin@erp.test',     'name' => 'Admin',               'role' => 'admin'],
            ['email' => 'inventory@erp.test', 'name' => 'Inventory Manager',    'role' => 'inventory_manager'],
            ['email' => 'sales@erp.test',     'name' => 'Sales Executive',      'role' => 'sales_executive'],
            ['email' => 'viewer@erp.test',    'name' => 'Viewer / Accountant',  'role' => 'viewer'],
            ['email' => 'customer@erp.test',  'name' => 'Demo Customer',        'role' => 'customer'],
        ];

        foreach ($users as $u) {
            $user = User::firstOrCreate(
                ['email' => $u['email']],
                ['name'  => $u['name'], 'password' => Hash::make('password')]
            );
            $user->syncRoles([$u['role']]);
        }

        $this->command->info('');
        $this->command->info('✅ 5 roles seeded:');
        $this->command->info('   admin@erp.test      / password  → Admin (full access)');
        $this->command->info('   inventory@erp.test  / password  → Inventory Manager');
        $this->command->info('   sales@erp.test      / password  → Sales Executive');
        $this->command->info('   viewer@erp.test     / password  → Viewer / Accountant');
        $this->command->info('   customer@erp.test   / password  → Customer (products + orders only)');
    }
}
