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
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('patient_id')->nullable();
            /* $table->enum('type', ['drug', 'lab', 'radiology', 'biopsy', 'sonography', 'mri']); */
            $table->text('description')->nullable();
            $table->string('tracking_code')->unique();
            $table->enum('status', ['pending', 'paid', 'rejected', 'completed'])->default('pending');
            $table->unsignedBigInteger('insurance_id')->nullable();
            $table->unsignedInteger('price')->nullable();
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending');
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('set null');
            $table->foreign('patient_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('insurance_id')->references('id')->on('insurances')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_requests');
    }
};
