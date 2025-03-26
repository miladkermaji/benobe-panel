<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('doctor_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('clinic_id')->nullable(); // برای نوبت حضوری
            $table->enum('appointment_type', ['in_person','online_phone','online_text','online_video'])->default('in_person');
            $table->text('notes')->nullable()->comment('توضیحات و ملاحظات پزشک برای این نوع نوبت');
            $table->timestamps();

            $table->foreign('doctor_id')
                ->references('id')
                ->on('doctors')
                ->onDelete('cascade');
            $table->foreign('clinic_id')
                ->references('id')
                ->on('clinics')
                ->onDelete('cascade');
            $table->unique(['doctor_id', 'clinic_id', 'appointment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_notes');
    }
};
