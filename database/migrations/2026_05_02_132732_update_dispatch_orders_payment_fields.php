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
        Schema::table('dispatch_orders', function (Blueprint $table) {

        if (!Schema::hasColumn('dispatch_orders', 'paid_amount')) {
            $table->decimal('paid_amount', 10, 2)->default(0);
        }

        if (!Schema::hasColumn('dispatch_orders', 'payment_status')) {
            $table->enum('payment_status', ['pending','partial','paid'])->default('pending');
        }

    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
