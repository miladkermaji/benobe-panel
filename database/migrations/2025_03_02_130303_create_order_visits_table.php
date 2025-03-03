<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('clinic_id')->nullable(); // کلینیک (nullable)
            $table->string('mobile', 11)->index();
            $table->timestamp('payment_date')->nullable();
            $table->string('bank_ref_id')->nullable()->index();
            $table->string('tracking_code')->unique()->index();
            $table->enum('payment_method', ['online', 'manual', 'free'])->default('free');
            $table->unsignedInteger('amount')->default(0);
            $table->timestamp('appointment_date')->nullable();
            $table->string('appointment_time')->nullable();
            $table->string('center_name')->nullable();
            $table->unsignedInteger('visit_cost')->default(0);
            $table->unsignedInteger('service_cost')->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_visits');
    }
};