<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('medical_center_selected_doctors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medical_center_id');
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->timestamps();

            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');

            $table->unique(['medical_center_id'], 'medical_center_doctor_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_center_selected_doctors');
    }
};
