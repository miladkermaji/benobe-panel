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
        Schema::create('doctor_medical_center', function (Blueprint $table) {


            $table->id();
            $table->unsignedBigInteger('medical_center_id');
            $table->unsignedBigInteger('doctor_id');
            $table->timestamps();

            $table->foreign('medical_center_id')
                  ->references('id')
                  ->on('medical_centers')
                  ->onDelete('cascade');
            $table->foreign('doctor_id')
                  ->references('id')
                  ->on('doctors')
                  ->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_medical_center');
    }
};
