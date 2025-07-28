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
        Schema::create('doctor_counseling_configs', function (Blueprint $table) {
            $table->id(); // شناسه منحصر به فرد

            $table->unsignedBigInteger('doctor_id');             // شناسه پزشک
            $table->unsignedBigInteger('medical_center_id')->nullable(); // شناسه مرکز درمانی (اختیاری)
            $table->boolean('has_phone_counseling')->default(false);
            // آیا مشاوره تلفنی ارائه می‌دهد؟

            $table->boolean('has_text_counseling')->default(false);
            // آیا مشاوره متنی ارائه می‌دهد؟

            $table->boolean('has_video_counseling')->default(false);
            // آیا مشاوره تصویری ارائه می‌دهد؟

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
            // مثال: ["phone", "text", "video"]

            $table->decimal('price_15min', 10, 2)->nullable();
            // قیمت مشاوره 15 دقیقه‌ای

            $table->decimal('price_30min', 10, 2)->nullable();
            // قیمت مشاوره 30 دقیقه‌ای

            $table->decimal('price_45min', 10, 2)->nullable();
            // قیمت مشاوره 45 دقیقه‌ای

            $table->decimal('price_60min', 10, 2)->nullable();
            // قیمت مشاوره 60 دقیقه‌ای

            $table->json('working_days')->nullable();
            // روزهای کاری در فرمت JSON
            // مثال: ["saturday", "sunday", "monday"]

            $table->boolean('active')->default(true);
            // آیا تنظیمات فعال است؟

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
        Schema::dropIfExists('doctor_counseling_configs');
    }
};
