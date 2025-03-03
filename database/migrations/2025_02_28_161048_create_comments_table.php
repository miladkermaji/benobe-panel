<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
    public function up()
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // نام کاربر
            $table->string('email')->nullable(); // ایمیل کاربر
            $table->string('ip')->nullable(); // آدرس IP
            $table->text('comment'); // متن نظر
            $table->text('reply')->nullable(); // پاسخ به نظر
            $table->boolean('approve')->default(false); // وضعیت (فعال/غیرفعال)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('comments');
    }
}