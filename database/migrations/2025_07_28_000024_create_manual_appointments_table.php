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
        Schema::create('manual_appointments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('medical_center_id')->nullable();
            $table->unsignedBigInteger('insurance_id')->nullable();
            $table->date('appointment_date');
            $table->time('appointment_time');
            $table->text('description')->nullable();
            $table->enum('status', ['scheduled', 'cancelled', 'attended', 'missed', 'pending_review'])->default('scheduled');
            $table->enum('payment_method', ['online', 'cash', 'card_to_card', 'pos'])->default('online')->nullable();
            $table->enum('payment_status', ['paid', 'unpaid', 'pending'])->default('unpaid');
            $table->string('tracking_code')->nullable();
            $table->decimal('fee', 8, 2)->nullable();
            $table->decimal('final_price', 14, 2)->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('set null');
            $table->foreign('insurance_id')->references('id')->on('insurances')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_appointments');
    }
};
