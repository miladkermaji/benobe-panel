<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospital_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('hospital_id');
            $table->string('image_path');                  // مسیر تصویر
            $table->string('caption')->nullable();         // توضیح تصویر
            $table->boolean('is_primary')->default(false); // تصویر اصلی
            $table->timestamps();

            $table->foreign('hospital_id')->references('id')->on('hospitals')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospital_galleries');
    }
};
