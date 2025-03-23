<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('treatment_center_galleries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('treatment_center_id');
            $table->string('image_path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->foreign('treatment_center_id')
                ->references('id')
                ->on('treatment_centers')
                ->onDelete('cascade');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('treatment_center_galleries');
    }
};
