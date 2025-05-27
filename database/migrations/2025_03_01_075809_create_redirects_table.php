<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRedirectsTable extends Migration
{
    public function up()
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('source_url')->unique()->comment('URL مبدا');
            $table->string('target_url')->comment('URL مقصد');
            $table->integer('status_code')->default(301)->comment('کد وضعیت HTTP (301 یا 302)');
            $table->boolean('is_active')->default(true)->comment('وضعیت فعال/غیرفعال');
            $table->text('description')->nullable()->comment('توضیحات اختیاری');
            $table->timestamps();

            // اضافه کردن ایندکس‌ها
            $table->index('is_active');
            $table->index('status_code');
            $table->index(['source_url', 'is_active']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('redirects');
    }
}
