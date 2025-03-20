<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imaging_center_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('imaging_center_id');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('imaging_center_id')->references('id')->on('imaging_centers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imaging_center_galleries');
    }
};
