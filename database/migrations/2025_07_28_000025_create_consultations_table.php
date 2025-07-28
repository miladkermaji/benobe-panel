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
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->unsignedBigInteger('medical_center_id')->nullable();
            $table->unsignedBigInteger('insurance_id')->nullable();
            $table->integer('duration')->nullable();
            $table->enum('consultation_type', ['general', 'specialized', 'emergency'])->nullable();
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('payment_status', ['pending', 'paid', 'unpaid'])->default('pending');
            $table->enum('consultation_mode', ['in_person', 'online', 'phone'])->default('in_person');
            $table->date('consultation_date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->timestamp('reserved_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->enum('status', ['scheduled', 'cancelled', 'attended', 'missed', 'pending_review'])->default('scheduled');
            $table->enum('attendance_status', ['attended', 'missed', 'cancelled'])->nullable();
            $table->text('notes')->nullable();
            $table->string('topic')->nullable();
            $table->string('tracking_code')->nullable()->unique();
            $table->decimal('fee', 8, 2)->nullable();
            $table->boolean('notification_sent')->default(false);
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('set null');
            $table->foreign('insurance_id')->references('id')->on('insurances')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
