<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('crawl_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url'); // URL کراول‌شده
            $table->string('status')->default('pending'); // وضعیت: pending, crawled, failed
            $table->text('message')->nullable(); // پیام خطا یا توضیحات
            $table->timestamps(); // created_at و updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crawl_logs');
    }
};
