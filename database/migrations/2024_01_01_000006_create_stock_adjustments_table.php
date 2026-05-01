<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['add', 'subtract']);   // add = stock in, subtract = stock out
            $table->unsignedInteger('quantity');
            $table->integer('quantity_before');          // snapshot for audit trail
            $table->integer('quantity_after');           // snapshot for audit trail
            $table->string('reason');                    // damaged, found, correction, write-off, etc.
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_adjustments');
    }
};
