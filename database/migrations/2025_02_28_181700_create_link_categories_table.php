<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLinkCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('link_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام دسته‌بندی (مثل "پایین سایت")
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('link_categories');
    }
}