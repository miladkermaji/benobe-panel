<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('story_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->onDelete('cascade');

            // Polymorphic fields برای پشتیبانی از همه نوع کاربران
            $table->morphs('liker'); // این دو فیلد ایجاد می‌کنه: liker_type و liker_id

            $table->timestamps();

            // جلوگیری از لایک تکراری
            $table->unique(['story_id', 'liker_type', 'liker_id']);

            // Indexes
            $table->index(['story_id', 'created_at']);
            $table->index(['liker_type', 'liker_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_likes');
    }
};
