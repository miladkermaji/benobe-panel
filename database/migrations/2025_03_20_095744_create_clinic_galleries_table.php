<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinic_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clinic_id');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinic_galleries');
    }
};
