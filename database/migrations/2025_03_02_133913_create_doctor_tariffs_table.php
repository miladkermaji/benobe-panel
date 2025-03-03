<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('doctor_tariffs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id'); // کلید خارجی به doctors
            $table->unsignedInteger('visit_fee')->default(0); // تعرفه نوبت (به تومان)
            $table->unsignedInteger('site_fee')->default(0); // تعرفه ویزیت سایت (به تومان)
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_tariffs');
    }
};