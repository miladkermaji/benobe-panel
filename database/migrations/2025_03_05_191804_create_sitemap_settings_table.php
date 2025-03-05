<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sitemap_settings', function (Blueprint $table) {
            $table->id();
            $table->string('base_url')->default('https://emr-benobe.ir');
            $table->integer('maximum_depth')->default(10);
            $table->integer('total_crawl_limit')->default(100);
            $table->integer('delay_between_requests')->default(1000);
            $table->boolean('ignore_robots')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sitemap_settings');
    }
};