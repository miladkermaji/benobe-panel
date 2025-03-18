<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reviewable_id')->nullable(); // کلید خارجی اختیاری
            $table->string('reviewable_type')->nullable();           // نوع مدل اختیاری
            $table->string('name')->nullable();                      // نام کاربر یا نویسنده (برای ورود دستی)
            $table->text('comment')->nullable();                     // متن نظر (اختیاری برای ورود دستی)
            $table->text('image_path')->nullable();                  // مسیر عکس (برای آپلود تصویر)
            $table->tinyInteger('rating')->unsigned()->default(0);   // امتیاز (0 تا 5)
            $table->boolean('is_approved')->default(false);          // وضعیت تأیید نظر
            $table->timestamps();

            // ایندکس برای بهبود عملکرد
            $table->index(['reviewable_id', 'reviewable_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
