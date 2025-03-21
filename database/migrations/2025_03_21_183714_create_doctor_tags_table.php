<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
    {
        Schema::create('doctor_tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained()->onDelete('cascade'); // ارتباط مستقیم با پزشک
            $table->string('name'); // نام تگ (مثلاً "کمترین معطلی")
            $table->string('color')->nullable(); // رنگ تگ (مثلاً "green-100")
            $table->string('text_color')->nullable(); // رنگ متن تگ (مثلاً "green-700")
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_tags');
    }
};
