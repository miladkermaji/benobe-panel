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
        Schema::create('doctor_counseling_holidays', function (Blueprint $table) {
          
$table->id();
$table->unsignedBigInteger('doctor_id'); // ارتباط با جدول پزشکان
$table->unsignedBigInteger('clinic_id')->nullable(); // ارتباط با جدول پزشکان
$table->json('holiday_dates')->nullable(); // ذخیره تاریخ‌های تعطیلات در قالب JSON
$table->string('status')->default('active'); // فیلد کمکی برای وضعیت
$table->timestamps();

$table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
$table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_counseling_holidays');
    }
};
