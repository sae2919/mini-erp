<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 14, 2);
            $table->string('method');   // cash | upi | card | bank_transfer | cheque
            $table->string('reference')->nullable(); // UPI txn ID, cheque no, etc.
            $table->date('paid_at');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'paid_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('payments');
    }
};
