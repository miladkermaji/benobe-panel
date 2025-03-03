<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrontPagesTable extends Migration
{
    public function up()
    {
        Schema::create('front_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_url')->unique(); // آدرس صفحه
            $table->string('title'); // عنوان
            $table->string('image')->nullable(); // تصویر
            $table->text('lead')->nullable(); // توضیحات کوتاه
            $table->text('description')->nullable(); // توضیحات کامل
            $table->boolean('approve')->default(true); // وضعیت انتشار
            $table->string('tplname')->nullable(); // قالب جداگانه
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('front_pages');
    }
}