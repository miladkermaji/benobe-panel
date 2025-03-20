<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFooterContentsTable extends Migration
{
    public function up()
    {
        Schema::create('footer_contents', function (Blueprint $table) {
            $table->id();
            $table->string('section')->index();          // نام بخش (مثل about، links، download، social)
            $table->string('title')->nullable();         // عنوان بخش (مثل "سامانه نوبت دهی...")
            $table->text('description')->nullable();     // توضیحات متنی
            $table->string('link_url')->nullable();      // آدرس لینک
            $table->string('link_text')->nullable();     // متن قابل نمایش لینک
            $table->string('icon_path')->nullable();     // مسیر فایل آیکون
            $table->string('image_path')->nullable();    // مسیر فایل تصویر (مثل لوگوها)
            $table->integer('order')->default(0);        // ترتیب نمایش
            $table->boolean('is_active')->default(true); // فعال/غیرفعال
            $table->string('language')->default('fa');   // زبان (برای آینده)
            $table->json('extra_data')->nullable();      // داده‌های اضافی (مثل استایل یا تنظیمات خاص)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('footer_contents');
    }
}
