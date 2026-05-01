<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('reference')->unique(); // auto-generated invoice number
            $table->string('customer_name')->nullable();
            $table->date('sale_date');
            $table->decimal('total_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sale_date');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('selling_price', 12, 2);  // price at time of sale
            $table->decimal('cost_price', 12, 2);     // snapshot for profit calc
            $table->decimal('subtotal', 14, 2)->storedAs('quantity * selling_price');
            $table->timestamps();

            $table->index(['sale_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};
