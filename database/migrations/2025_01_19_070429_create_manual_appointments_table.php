<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('manual_appointments', function (Blueprint $table) {
            $table->id(); // شناسه یکتا
            $table->unsignedBigInteger('user_id'); // شناسه کاربر از جدول users
            $table->unsignedBigInteger('doctor_id'); // شناسه پزشک
            $table->unsignedBigInteger('insurance_id')->nullable(); // شناسه بیمه
            $table->unsignedBigInteger('clinic_id')->nullable(); // شناسه پزشک
            $table->date('appointment_date'); // تاریخ نوبت
            $table->time('appointment_time'); // ساعت نوبت
            $table->text('description')->nullable(); // توضیحات
            $table->string('status')->default('scheduled'); // وضعیت نوبت (scheduled, canceled, etc.)
            $table->enum('payment_method', ['online', 'cash', 'card_to_card', 'pos'])->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'unpaid'])->default('pending')->nullable();
            $table->decimal('fee', 8, 2)->nullable();
            $table->decimal('final_price', 14, 2)->nullable();
            $table->string('tracking_code')->nullable()->unique();
            $table->timestamps(); // زمان‌های ایجاد و به‌روزرسانی

            // تعریف کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            // اضافه کردن ایندکس‌ها
            $table->index('appointment_date');
            $table->index('status');
            $table->index('payment_status');
            $table->index('payment_method');
            $table->index('tracking_code');
            $table->index(['doctor_id', 'appointment_date']);
            $table->index(['clinic_id', 'appointment_date']);
            $table->index(['status', 'payment_status']);
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
