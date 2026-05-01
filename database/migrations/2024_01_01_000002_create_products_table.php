<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('sku')->unique();
            $table->decimal('price', 12, 2);        // selling price
            $table->decimal('cost_price', 12, 2);   // purchase cost
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(10);
            $table->string('unit')->default('pcs'); // pcs, kg, litre, etc.
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active']);
            $table->index('stock_quantity');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
