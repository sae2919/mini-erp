<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'production_cost')) {
                $table->decimal('production_cost', 14, 2)->default(0)->after('cost_price');
            }
            if (!Schema::hasColumn('products', 'dispatch_price')) {
                $table->decimal('dispatch_price', 14, 2)->default(0)->after('production_cost');
            }
            if (!Schema::hasColumn('products', 'mrp')) {
                $table->decimal('mrp', 14, 2)->default(0)->after('dispatch_price');
            }
            if (!Schema::hasColumn('products', 'commission_rate')) {
                $table->decimal('commission_rate', 5, 2)->default(0)->after('mrp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = ['production_cost','dispatch_price','mrp','commission_rate'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
