<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rule_id')->nullable()->constrained('transaction_rules')->nullOnDelete();
            $table->string('title', 255);
            $table->unsignedInteger('amount');
            $table->enum('type', ['income', 'expense']);
            $table->date('scheduled_date');
            $table->date('actual_date')->nullable();
            $table->enum('status', ['scheduled', 'completed', 'carried_over', 'cancelled'])->default('scheduled');
            $table->unsignedBigInteger('carried_over_from')->nullable();
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();
            $table->text('memo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('carried_over_from')->references('id')->on('transactions')->nullOnDelete();
            $table->index(['user_id', 'scheduled_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
