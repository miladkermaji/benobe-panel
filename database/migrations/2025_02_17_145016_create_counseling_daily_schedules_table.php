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
        Schema::create('counseling_daily_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id'); // ارتباط با پزشک
            $table->unsignedBigInteger('clinic_id')->nullable(); // ارتباط با کلینیک
            $table->date('date'); // تاریخ روز خاص
            $table->json('consultation_hours'); // ساعات مشاوره به‌صورت JSON

            $table->json('appointment_settings')->nullable()->comment(' زمانبندی باز شدن نوبت ها');
            $table->json('emergency_times')->nullable()->comment(' زمان های اورژانسی');

            $table->string('consultation_type')->nullable(); // نوع مشاوره (مثلاً آنلاین یا حضوری)
            $table->timestamps();

            // کلید خارجی
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counseling_daily_schedules');
    }
};
