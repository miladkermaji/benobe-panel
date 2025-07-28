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
        Schema::create('manual_appointment_settings', function (Blueprint $table) {
            $table->id(); // کلید اصلی
            $table->foreignId('doctor_id')
                ->constrained('doctors') // اتصال به جدول doctors
                ->onDelete('cascade') // حذف تنظیمات در صورت حذف پزشک
                ->comment('آی‌دی پزشک');
            $table->unsignedBigInteger('medical_center_id')->nullable()->comment('آی‌دی مرکز درمانی');

            $table->boolean('is_active')->default(1)->comment('فعال بودن تایید دو مرحله‌ای (1 = بلی, 0 = خیر)');
            $table->unsignedInteger('duration_send_link')->default(3)->comment('زمان ارسال لینک تایید به ساعت');
            $table->unsignedInteger('duration_confirm_link')->default(1)->comment('مدت اعتبار لینک تایید به ساعت');
            $table->timestamps(); // تاریخ ایجاد و بروزرسانی
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_appointment_settings');
    }
};
