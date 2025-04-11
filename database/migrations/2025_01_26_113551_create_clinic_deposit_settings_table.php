<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clinic_deposit_settings', function (Blueprint $table) {
            $table->id(); // کلید اصلی
            $table->unsignedBigInteger('doctor_id'); // شناسه پزشک
            $table->unsignedBigInteger('clinic_id')->nullable(); // شناسه مطب
            $table->decimal('deposit_amount', 10, 2)->nullable(); // مبلغ بیعانه
            $table->boolean('is_custom_price')->default(false); // آیا مبلغ دلخواه است؟
            $table->boolean('refundable')->default(true); // آیا مبلغ قابل استرداد است؟
            $table->boolean('is_active')->default(true); // آیا تنظیمات فعال است؟
            $table->text('notes')->nullable(); // توضیحات اضافی یا یادداشت
            $table->timestamps(); // زمان ایجاد و بروزرسانی

            // روابط
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clinic_deposit_settings');
    }
};
