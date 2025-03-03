<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wallet_reports', function (Blueprint $table) {
            $table->id();
            $table->dateTime('report_date'); // تاریخ ثبت
            $table->text('description'); // توضیح
            $table->decimal('amount', 15, 2); // مبلغ
            $table->string('status'); // وضعیت (مثلاً "در انتظار درخواست"، "پرداخت‌شده")
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_reports');
    }
};