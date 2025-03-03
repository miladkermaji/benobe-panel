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
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->cascadeOnDelete();
            $table->foreignId('clinic_id')->nullable()->constrained('clinics')->nullOnDelete();
            $table->string('name');
            $table->tinyInteger('calculation_method'); // 0, 1, 2
            $table->unsignedInteger('appointment_price')->nullable(); // مبلغ نوبت (تومان)
            $table->unsignedInteger('insurance_percent')->nullable(); // درصد سهم بیمه
            $table->unsignedInteger('final_price')->nullable(); // مبلغ تمام‌شده (تومان)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
