<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('prescription_insulin_request', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_request_id')->constrained('prescription_requests')->onDelete('cascade');
            $table->foreignId('insulin_id')->constrained('insulins')->onDelete('cascade');
            $table->integer('count');
            $table->unique(['prescription_request_id', 'insulin_id'], 'presc_insulin_req_unique');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('prescription_insulin_request');
    }
};
