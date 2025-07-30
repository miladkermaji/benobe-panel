<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('walletable_id')->nullable();
            $table->string('walletable_type')->nullable();
            $table->unsignedInteger('amount')->default(0);
            $table->enum('status', ['pending', 'available', 'requested', 'paid'])->default('pending');
            $table->enum('type', ['deposit', 'withdrawal', 'payment'])->default('payment');
            $table->string('description')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // اضافه کردن ایندکس‌ها
            $table->index('status');
            $table->index('type');
            $table->index('registered_at');
            $table->index('paid_at');
            $table->index(['walletable_id', 'walletable_type']);
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_wallet_transactions');
    }
};
