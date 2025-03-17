<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banner_texts', function (Blueprint $table) {
            $table->id();
            $table->string('main_text');                       // متن اصلی
            $table->json('switch_words');                      // کلمات متغیر
            $table->integer('switch_interval')->default(2000); // فاصله زمانی تغییر
            $table->string('image_path')->nullable();          // مسیر تصویر بنر
            $table->boolean('status')->default(1);             // فعال/غیرفعال
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('banner_texts');
    }
};
