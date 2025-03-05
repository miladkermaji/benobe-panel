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
        Schema::create('sitemap_urls', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->float('priority')->default(0.8);
            $table->enum('frequency', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('weekly');
            $table->string('type')->default('page'); // برای جدا کردن صفحات، تصاویر و ویدیوها
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sitemap_urls');
    }
};
