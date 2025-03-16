<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ایجاد جدول insurances
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->nullOnDelete();
            $table->string('name');
            $table->tinyInteger('calculation_method')->default(0);
            $table->unsignedInteger('appointment_price')->nullable();
            $table->unsignedInteger('insurance_percent')->nullable();
            $table->unsignedInteger('final_price')->nullable();
            $table->timestamps();
        });

        // اجرای Seeder با مدیریت خطا
        try {
            Artisan::call('db:seed', [
                '--class' => 'InsurancesSeeder',
            ]);
            // (اختیاری) نمایش خروجی
            // echo Artisan::output();
        } catch (\Exception $e) {
            // در صورت خطا، فقط هشدار می‌ده و Migration ادامه می‌ده
            \Log::warning('اجرای Seeder با خطا مواجه شد: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
