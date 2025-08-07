<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        // ایجاد جدول insurances
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_center_id')->nullable()->constrained('medical_centers')->nullOnDelete();
            $table->string('name');

            $table->string('slug')->unique();

            $table->tinyInteger('calculation_method')->default(0);
            $table->unsignedInteger('appointment_price')->nullable();
            $table->unsignedInteger('insurance_percent')->nullable();
            $table->unsignedInteger('final_price')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();

            // اضافه کردن ایندکس‌ها
            $table->index('medical_center_id');
            $table->index('name');
            $table->index('calculation_method');
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
            Log::warning('اجرای Seeder با خطا مواجه شد: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
