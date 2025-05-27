<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('url')->nullable();
            $table->string('icon')->nullable();
            $table->enum('position', ['top', 'bottom', 'top_bottom'])->default('top');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('menus')->onDelete('cascade');

            // اضافه کردن ایندکس‌ها
            $table->index('position');
            $table->index('status');
            $table->index('parent_id');
            $table->index(['position', 'status']);
            $table->index('order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
