<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');           // created, updated, deleted, login, export, etc.
            $table->string('module');           // Sale, Product, Purchase, etc.
            $table->string('description');      // Human-readable summary
            $table->unsignedBigInteger('subject_id')->nullable();   // ID of affected record
            $table->string('ip_address', 45)->nullable();
            $table->json('properties')->nullable();                 // before/after data
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['module', 'subject_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
