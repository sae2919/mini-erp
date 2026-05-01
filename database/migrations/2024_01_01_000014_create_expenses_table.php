<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color')->default('#6366f1');
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expense_category_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->decimal('amount', 14, 2);
            $table->date('expense_date');
            $table->string('payment_method')->default('cash');
            $table->string('reference')->nullable();
            $table->string('receipt')->nullable();   // file path
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['expense_category_id', 'expense_date']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
