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
        Schema::create('story_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->onDelete('cascade');

            // Polymorphic fields برای پشتیبانی از همه نوع کاربران
            $table->morphs('viewer'); // این دو فیلد ایجاد می‌کنه: viewer_type و viewer_id

            $table->string('ip_address')->nullable(); // آدرس IP
            $table->string('user_agent')->nullable(); // User Agent
            $table->string('session_id')->nullable(); // شناسه نشست
            $table->timestamp('viewed_at');

            // Indexes
            $table->index(['story_id', 'viewed_at']);
            $table->index(['viewer_type', 'viewer_id', 'viewed_at']);
            $table->index(['ip_address', 'story_id']);
            $table->index('viewed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('story_views');
    }
};
