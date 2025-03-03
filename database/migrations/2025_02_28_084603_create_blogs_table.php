<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('category_id');
            $table->dateTime('date');
            $table->text('short_description')->nullable();
            $table->text('content')->nullable();
            $table->string('image')->nullable();
            $table->integer('views')->default(0);
            $table->integer('comments_count')->default(0);
            $table->boolean('is_index')->default(false);
            $table->boolean('status')->default(false);
            $table->string('page_title')->nullable();
            $table->string('url_seo')->nullable();
            $table->text('meta_description')->nullable();
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('category_blogs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');

    }
};
