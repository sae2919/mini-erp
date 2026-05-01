<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('erp_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type');         // low_stock | new_order | payment_received | overdue | return
            $table->string('title');
            $table->text('message');
            $table->string('icon')->default('🔔');
            $table->string('color')->default('indigo');
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('erp_notifications');
    }
};
