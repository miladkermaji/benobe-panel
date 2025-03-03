<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBestDoctorsTable extends Migration
{
    public function up()
    {
        Schema::create('best_doctors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id');
            $table->unsignedBigInteger('hospital_id')->nullable();
            $table->boolean('best_doctor')->default(false);
            $table->boolean('best_consultant')->default(false);
            $table->boolean('status')->default(true);
            $table->timestamps();

            // کلیدهای خارجی
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('best_doctors');
    }
}
