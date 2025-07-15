<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('prescription_request_insurance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('prescription_request_id');
            $table->unsignedBigInteger('prescription_insurance_id');
            $table->string('referral_code')->nullable();
            $table->timestamps();
            $table->unique(['prescription_request_id', 'prescription_insurance_id'], 'presc_req_ins_unique');
            $table->foreign('prescription_request_id')->references('id')->on('prescription_requests')->onDelete('cascade');
            $table->foreign('prescription_insurance_id')->references('id')->on('prescription_insurances')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('prescription_request_insurance');
    }
};
