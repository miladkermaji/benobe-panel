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
        Schema::create('doctor_holidays', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id'); // ارتباط با جدول پزشکان
            $table->unsignedBigInteger('medical_center_id')->nullable(); // ارتباط با جدول مراکز درمانی
            $table->json('holiday_dates')->nullable(); // ذخیره تاریخ‌های تعطیلات در قالب JSON
            $table->string('status')->default('active'); // فیلد کمکی برای وضعیت
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_holidays');
    }
};
