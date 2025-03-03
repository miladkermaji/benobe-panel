<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSitemapLinksTable extends Migration
{
    public function up()
    {
        Schema::create('sitemap_links', function (Blueprint $table) {
            $table->id();
            $table->string('url')->unique();
            $table->float('priority')->default(0.5); // اولویت (0.0 تا 1.0)
            $table->enum('changefreq', ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'])->default('monthly'); // فرکانس تغییر
            $table->dateTime('lastmod')->nullable(); // آخرین تغییر
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sitemap_links');
    }
}