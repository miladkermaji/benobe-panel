<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('prescription_requests', function (Blueprint $table) {
            $table->id();
            $table->morphs('requestable'); // user, doctor, manager, secretary
            $table->enum('type', ['renew_lab', 'renew_drug', 'renew_insulin', 'sonography', 'mri', 'other'])->nullable();
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            $table->string('description', 80)->nullable();
            $table->text('doctor_description')->nullable(); // توضیحات پزشک
            $table->unsignedBigInteger('tracking_code')->nullable();
            $table->enum('status', ['pending', 'paid', 'rejected', 'completed'])->default('pending');
            $table->unsignedBigInteger('prescription_insurance_id')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->unsignedBigInteger('medical_center_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->tinyInteger('request_enabled')->default(0);
            $table->json('enabled_types')->nullable();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('prescription_insurance_id')->references('id')->on('prescription_insurances')->onDelete('set null');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('set null');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_requests');
    }
};
