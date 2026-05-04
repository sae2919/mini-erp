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
    Schema::table('products', function (Blueprint $table) {
        if (!Schema::hasColumn('products', 'is_available_online')) {
            $table->boolean('is_available_online')->default(false);
        }
        if (!Schema::hasColumn('products', 'is_featured')) {
            $table->boolean('is_featured')->default(false);
        }
        if (!Schema::hasColumn('products', 'description_long')) {
            $table->text('description_long')->nullable();
        }
    });
}

public function down(): void
{
    Schema::table('products', function (Blueprint $table) {
        $table->dropColumn(['is_available_online', 'is_featured', 'description_long']);
    });
}
};
