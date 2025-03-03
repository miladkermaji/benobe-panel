<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manual_appointments', function (Blueprint $table) {
            $table->id(); // شناسه یکتا
            $table->unsignedBigInteger('user_id'); // شناسه کاربر از جدول users
            $table->unsignedBigInteger('doctor_id'); // شناسه پزشک
            $table->unsignedBigInteger('clinic_id')->nullable(); // شناسه پزشک
            $table->date('appointment_date'); // تاریخ نوبت
            $table->time('appointment_time'); // ساعت نوبت
            $table->text('description')->nullable(); // توضیحات
            $table->string('status')->default('scheduled'); // وضعیت نوبت (scheduled, canceled, etc.)
            $table->timestamps(); // زمان‌های ایجاد و به‌روزرسانی

            // تعریف کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_appointments');
    }
};
