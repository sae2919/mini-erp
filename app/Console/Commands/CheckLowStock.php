<?php

namespace App\Console\Commands;

use App\Mail\LowStockAlert;
use App\Models\Product;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckLowStock extends Command
{
    protected $signature   = 'inventory:check-low-stock';
    protected $description = 'Send low stock alert email to admin if any products are below threshold';

    public function handle(): int
    {
        $lowStockProducts = Product::active()
            ->lowStock()
            ->with('category')
            ->orderBy('stock_quantity')
            ->get();

        if ($lowStockProducts->isEmpty()) {
            $this->info('All products are sufficiently stocked. No alert sent.');
            return self::SUCCESS;
        }

        // Get all admin users
        $admins = User::role('admin')->get();

        if ($admins->isEmpty()) {
            $this->warn('No admin users found. Alert not sent.');
            return self::SUCCESS;
        }

        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new LowStockAlert($lowStockProducts));
            $this->info("Alert sent to {$admin->email}");
        }

        $this->info("Low stock alert sent for {$lowStockProducts->count()} product(s).");

        return self::SUCCESS;
    }
}
