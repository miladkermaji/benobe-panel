<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeVideosTable extends Migration
{
    public function up()
    {
        Schema::create('home_videos', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // عنوان ویدئو
            $table->string('image')->nullable(); // مسیر تصویر
            $table->string('video')->nullable(); // مسیر فایل ویدئو
            $table->text('description')->nullable(); // توضیحات
            $table->boolean('approve')->default(true); // وضعیت نمایش (فعال/غیرفعال)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('home_videos');
    }
}