<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinksTable extends Migration
{
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // عنوان پیوند
            $table->unsignedBigInteger('category_id'); // دسته‌بندی
            $table->string('url'); // آدرس URL
            $table->string('rel')->nullable(); // rel (مثل nofollow)
            $table->boolean('approve')->default(true); // وضعیت نمایش
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('link_categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('links');
    }
}