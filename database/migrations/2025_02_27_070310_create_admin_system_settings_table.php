<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin_system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // کلید تنظیم (مثلاً home_title)
            $table->text('value')->nullable(); // مقدار تنظیم
            $table->string('type')->default('string'); // نوع داده (string, integer, boolean, json)
            $table->string('group')->default('general'); // گروه‌بندی تنظیمات (general, seo, payment, etc)
            $table->text('description')->nullable(); // توضیحات برای هر تنظیم
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_system_settings');
    }
};
