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
        Schema::create('doctor_appointment_configs', function (Blueprint $table) {
            $table->id(); // شناسه منحصر به فرد

            $table->unsignedBigInteger('doctor_id'); // شناسه پزشک
            $table->unsignedBigInteger('medical_center_id')->nullable(); // شناسه مرکز درمانی

            $table->boolean('auto_scheduling')->default(true);
            // آیا نوبت‌دهی به صورت خودکار انجام شود؟

            $table->integer('calendar_days')->default(30);
            // تعداد روزهای باز در تقویم نوبت‌دهی

            $table->boolean('online_consultation')->default(false);
            // آیا مشاوره آنلاین فعال باشد؟

            $table->boolean('holiday_availability')->default(false);
            // آیا در تعطیلات امکان نوبت‌دهی وجود دارد؟

            $table->integer('appointment_duration')->default(15);
            // مدت زمان هر نوبت (دقیقه)

            $table->boolean('collaboration_with_other_sites')->default(false);
            // آیا همکاری با سایر سایت‌های نوبت‌دهی وجود دارد؟

            $table->json('consultation_types')->nullable();
            // انواع مشاوره در فرمت JSON
            // مثال: ["general", "specialized", "emergency"]

            // Manual appointment settings fields
            $table->boolean('is_active')->default(1)->comment('فعال بودن تایید دو مرحله‌ای (1 = بلی, 0 = خیر)');
            $table->unsignedInteger('duration_send_link')->default(3)->comment('زمان ارسال لینک تایید به ساعت');
            $table->unsignedInteger('duration_confirm_link')->default(1)->comment('مدت اعتبار لینک تایید به ساعت');

            $table->timestamps(); // زمان ایجاد و آخرین بروزرسانی

            // تعریف کلیدهای خارجی
            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('cascade'); // حذف تنظیمات در صورت حذف پزشک

            $table->foreign('medical_center_id')
                ->references('id')
                ->on('medical_centers')
                ->onDelete('cascade'); // حذف تنظیمات در صورت حذف مرکز درمانی

            // محدودیت یکتایی - فقط یک رکورد برای هر پزشک و مرکز درمانی
            $table->unique(['doctor_id', 'medical_center_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_appointment_configs');
    }
};
