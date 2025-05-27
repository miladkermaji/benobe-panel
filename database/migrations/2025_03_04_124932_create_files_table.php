<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('path')->unique();
            $table->string('type')->nullable(); // folder or file
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('size')->nullable(); // اندازه فایل در بایت
            $table->timestamps();

            // اضافه کردن ایندکس‌ها
            $table->index('type');
            $table->index('extension');
            $table->index(['type', 'extension']);
            $table->index('size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
