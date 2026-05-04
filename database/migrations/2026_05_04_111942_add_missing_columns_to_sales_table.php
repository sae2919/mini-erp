<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('sales', function (Blueprint $table) {
        if (!Schema::hasColumn('sales', 'customer_phone')) {
            $table->string('customer_phone')->nullable();
        }
        if (!Schema::hasColumn('sales', 'seller_id')) {
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();
        }
        if (!Schema::hasColumn('sales', 'seller_commission')) {
            $table->decimal('seller_commission', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('sales', 'company_receivable')) {
            $table->decimal('company_receivable', 10, 2)->default(0);
        }
        if (!Schema::hasColumn('sales', 'commission_status')) {
            $table->string('commission_status')->default('pending');
        }
    });
}

public function down(): void
{
    Schema::table('sales', function (Blueprint $table) {
        $table->dropColumn([
            'customer_phone',
            'seller_id',
            'seller_commission',
            'company_receivable',
            'commission_status',
        ]);
    });
}
};
