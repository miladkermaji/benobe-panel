<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('question_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام دسته‌بندی
            $table->string('alt_name')->nullable(); // نام جایگزین (مثلاً sport)
            $table->boolean('approve')->default(true);
            $table->timestamps();

            // اضافه کردن ایندکس‌ها
            $table->index('name');
            $table->index('approve');
            $table->index('alt_name');
            $table->index(['name', 'approve']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('question_categories');
    }
}
