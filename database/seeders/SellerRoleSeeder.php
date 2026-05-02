<?php

namespace Database\Seeders;

use App\Models\Seller;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class SellerRoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Add seller role (keeps all existing roles intact)
        Role::firstOrCreate(['name' => 'seller']);

        // Rename inventory_manager → manager if needed
        // (optional - only if you want to use the new role name)
        // Role::where('name','inventory_manager')->update(['name'=>'manager']);

        // Demo seller users
        $sellers = [
            ['name'=>'Dealer One',  'email'=>'seller1@erp.test', 'phone'=>'9876543210', 'region'=>'Telangana'],
            ['name'=>'Dealer Two',  'email'=>'seller2@erp.test', 'phone'=>'9876543211', 'region'=>'Andhra Pradesh'],
            ['name'=>'Dealer Three','email'=>'seller3@erp.test', 'phone'=>'9876543212', 'region'=>'Karnataka'],
        ];

        foreach ($sellers as $s) {
            $user = User::firstOrCreate(
                ['email' => $s['email']],
                ['name'  => $s['name'], 'password' => Hash::make('password')]
            );
            $user->syncRoles(['seller']);

            Seller::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'name'         => $s['name'],
                    'phone'        => $s['phone'],
                    'email'        => $s['email'],
                    'region'       => $s['region'],
                    'credit_limit' => 50000,
                    'balance_due'  => 0,
                    'is_active'    => true,
                ]
            );
        }

        $this->command->info('');
        $this->command->info('✅ Seller role + demo sellers added:');
        $this->command->info('   seller1@erp.test / password → Dealer One');
        $this->command->info('   seller2@erp.test / password → Dealer Two');
        $this->command->info('   seller3@erp.test / password → Dealer Three');
    }
}
