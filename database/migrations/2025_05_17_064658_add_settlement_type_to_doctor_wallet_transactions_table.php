<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('doctor_wallet_transactions', function (Blueprint $table) {
            $table->enum('type', ['online', 'in_person', 'wallet_charge', 'settlement'])->default('online')->change();
        });
    }

    public function down(): void
    {
        Schema::table('doctor_wallet_transactions', function (Blueprint $table) {
            $table->enum('type', ['online', 'in_person', 'wallet_charge'])->default('online')->change();
        });
    }
};
