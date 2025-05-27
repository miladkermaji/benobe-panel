<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMailTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('mail_templates', function (Blueprint $table) {
            $table->id();
            $table->string('subject')->unique();
            $table->text('template');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // اضافه کردن ایندکس‌ها
            $table->index('is_active');
            $table->index('subject');
        });
    }

    public function down()
    {
        Schema::dropIfExists('mail_templates');
    }
}
