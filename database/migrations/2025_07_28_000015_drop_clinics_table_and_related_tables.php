<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // حذف جداول مرتبط با clinics
        Schema::dropIfExists('clinic_galleries');
        
        // حذف جدول اصلی clinics
        Schema::dropIfExists('clinics');
    }

    public function down(): void
    {
        // بازسازی جدول clinics (ساده شده)
        Schema::create('clinics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('secretary_phone')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('postal_code')->nullable();
            $table->unsignedBigInteger('province_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->boolean('is_main_clinic')->default(false);
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('consultation_fee', 10, 2)->nullable();
            $table->decimal('prescription_fee', 10, 2)->nullable();
            $table->enum('payment_methods', ['cash', 'card', 'online'])->nullable();
            $table->boolean('is_active')->default(false);
            $table->json('working_days')->nullable();
            $table->text('avatar')->nullable();
            $table->json('documents')->nullable();
            $table->json('phone_numbers')->nullable();
            $table->boolean('location_confirmed')->default(false);
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('zone')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('zone')->onDelete('set null');
        });

        // بازسازی جدول clinic_galleries
        Schema::create('clinic_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }
}; 