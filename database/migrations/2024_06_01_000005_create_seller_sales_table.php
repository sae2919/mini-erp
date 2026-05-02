<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seller_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->string('reference')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->date('sale_date');
            $table->decimal('total_amount',       14, 2)->default(0);
            $table->decimal('commission_amount',  14, 2)->default(0);
            $table->decimal('company_amount',     14, 2)->default(0);
            $table->enum('payment_status', ['unpaid','partial','paid'])->default('unpaid');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('seller_sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('selling_price',     12, 2);
            $table->decimal('dispatch_price',    12, 2);
            $table->decimal('commission_rate',    5, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('subtotal',          14, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seller_sale_id')->constrained()->cascadeOnDelete();
            $table->decimal('amount', 14, 2);
            $table->enum('status', ['pending','paid'])->default('pending');
            $table->date('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('seller_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->cascadeOnDelete();
            $table->foreignId('dispatch_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('method')->default('cash');
            $table->string('reference')->nullable();
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seller_payments');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('seller_sale_items');
        Schema::dropIfExists('seller_sales');
    }
};
