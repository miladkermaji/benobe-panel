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
        Schema::create('vacations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('clinic_id')->nullable();
            $table->date('date'); // تاریخ مرخصی
            $table->time('start_time')->nullable(); // ساعت شروع مرخصی
            $table->time('end_time')->nullable(); // ساعت پایان مرخصی
            $table->boolean('is_full_day')->default(false); // آیا تمام روز مرخصی است؟
            $table->timestamps();
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');

            // اضافه کردن ایندکس‌ها
            $table->index('doctor_id');
            $table->index('clinic_id');
            $table->index('date');
            $table->index(['doctor_id', 'date']);
            $table->index(['clinic_id', 'date']);
            $table->index('is_full_day');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vacations');
    }
};
