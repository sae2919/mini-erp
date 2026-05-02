<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('sales', function (Blueprint $table) {
        $table->foreignId('seller_id')->nullable()->constrained('users');
        $table->decimal('seller_commission', 10, 2)->default(0);
        $table->enum('commission_status', ['pending', 'paid'])->default('pending');
    });

    Schema::table('sale_items', function (Blueprint $table) {
        $table->decimal('commission_rate', 5, 2)->default(0);
        $table->decimal('commission_amount', 10, 2)->default(0);
        $table->decimal('dispatch_price', 10, 2)->default(0);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales_and_items', function (Blueprint $table) {
            //
        });
    }
};
