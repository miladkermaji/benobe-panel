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
        Schema::create('sms_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // نام درایور (مثل pishgamrayan)
            $table->string('title'); // عنوان قابل نمایش (مثل "پیشگام رایان")
            $table->boolean('is_active')->default(false); // فعال یا غیرفعال
            $table->json('settings')->nullable(); // تنظیمات خاص هر درایور
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_gateways');
    }
};
