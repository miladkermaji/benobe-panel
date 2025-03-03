<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('slides', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // عنوان اسلاید
            $table->string('image'); // مسیر تصویر
            $table->string('link')->nullable(); // لینک نمایش
            $table->text('description')->nullable(); // توضیحات
            $table->enum('display', ['site', 'mobile'])->default('site'); // قابل نمایش در
            $table->boolean('status')->default(0); // وضعیت (فعال/غیرفعال)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('slides');
    }
};