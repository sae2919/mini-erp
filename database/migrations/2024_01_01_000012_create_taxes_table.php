<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Create taxes table ────────────────────────────────────
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('rate', 5, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // ── Add tax_id to products ────────────────────────────────
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'tax_id')) {
                $table->foreignId('tax_id')->nullable()->constrained('taxes')->nullOnDelete();
            }
        });

        // ── Add new columns to sales (safe - checks before adding) ─
        Schema::table('sales', function (Blueprint $table) {
            if (!Schema::hasColumn('sales', 'status')) {
                $table->string('status')->default('completed');
            }
            if (!Schema::hasColumn('sales', 'order_type')) {
                $table->string('order_type')->default('offline');
            }
            if (!Schema::hasColumn('sales', 'shipping_name')) {
                $table->string('shipping_name')->nullable();
            }
            if (!Schema::hasColumn('sales', 'shipping_phone')) {
                $table->string('shipping_phone', 20)->nullable();
            }
            if (!Schema::hasColumn('sales', 'shipping_email')) {
                $table->string('shipping_email')->nullable();
            }
            if (!Schema::hasColumn('sales', 'shipping_address')) {
                $table->text('shipping_address')->nullable();
            }
            if (!Schema::hasColumn('sales', 'subtotal_amount')) {
                $table->decimal('subtotal_amount', 14, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'tax_amount')) {
                $table->decimal('tax_amount', 14, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'discount_amount')) {
                $table->decimal('discount_amount', 14, 2)->default(0);
            }
            if (!Schema::hasColumn('sales', 'payment_status')) {
                $table->string('payment_status')->default('unpaid');
            }
        });

        // ── Add tax/discount to sale_items ────────────────────────
        Schema::table('sale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('sale_items', 'tax_rate')) {
                $table->decimal('tax_rate', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('sale_items', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0);
            }
            if (!Schema::hasColumn('sale_items', 'discount')) {
                $table->decimal('discount', 5, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $drop = array_filter(['tax_rate','tax_amount','discount'],
                fn($c) => Schema::hasColumn('sale_items', $c));
            if ($drop) $table->dropColumn(array_values($drop));
        });

        Schema::table('sales', function (Blueprint $table) {
            $cols = ['subtotal_amount','tax_amount','discount_amount','payment_status',
                     'status','order_type','shipping_name','shipping_phone',
                     'shipping_email','shipping_address'];
            $drop = array_filter($cols, fn($c) => Schema::hasColumn('sales', $c));
            if ($drop) $table->dropColumn(array_values($drop));
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'tax_id')) {
                $table->dropForeign(['tax_id']);
                $table->dropColumn('tax_id');
            }
        });

        Schema::dropIfExists('taxes');
    }
};
