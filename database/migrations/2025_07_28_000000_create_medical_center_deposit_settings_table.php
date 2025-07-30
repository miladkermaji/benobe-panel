<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('medical_center_deposit_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('medical_center_id');
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->boolean('is_custom_price')->default(false);
            $table->boolean('refundable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');

            $table->unique(['doctor_id', 'medical_center_id'], 'doctor_medical_center_deposit_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_center_deposit_settings');
    }
};
