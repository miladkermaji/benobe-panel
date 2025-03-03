<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // تغییر ستون type برای اضافه کردن 'charge'
        Schema::table('doctor_wallet_transactions', function (Blueprint $table) {
            $table->enum('type', ['online', 'in_person', 'charge'])->default('online')->change();
        });
    }

    public function down(): void
    {
        // برگشت به حالت قبلی (بدون 'charge')
        Schema::table('doctor_wallet_transactions', function (Blueprint $table) {
            $table->enum('type', ['online', 'in_person'])->default('online')->change();
        });
    }
};