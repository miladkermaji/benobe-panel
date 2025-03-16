<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ایجاد جدول services
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام خدمت
            $table->timestamps();
        });

        // اجرای Seeder با مدیریت خطا
        try {
            Artisan::call('db:seed', [
                '--class' => 'ServicesSeeder',
            ]);
        } catch (\Exception $e) {
            \Log::warning('اجرای Seeder با خطا مواجه شد: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
