<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('doctor_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('medical_center_id')->nullable()->constrained('medical_centers')->nullOnDelete();
            $table->unsignedInteger('amount')->default(0);
            $table->enum('status', ['pending', 'available', 'requested', 'paid'])->default('pending');
            $table->enum('type', ['online', 'in_person', 'charge', 'wallet_charge', 'settlement', 'settlement_reversal'])->default('online');
            $table->string('description')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['doctor_id', 'registered_at']);
            $table->index(['type', 'status']);
            $table->index('medical_center_id');
            $table->index('amount');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_wallet_transactions');
    }
};
