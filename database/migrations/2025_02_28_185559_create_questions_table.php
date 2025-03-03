<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category_id'); // دسته‌بندی
            $table->string('title'); // عنوان سوال
            $table->text('question'); // متن سوال
            $table->string('asker_name'); // نام پرسش‌کننده
            $table->string('asker_phone')->nullable(); // شماره تماس پرسش‌کننده
            $table->text('reply')->nullable(); // پاسخ
            $table->string('replier_name')->nullable(); // نام پاسخ‌دهنده
            $table->boolean('approve')->default(false); // وضعیت انتشار
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('question_categories')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
}