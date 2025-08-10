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
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable(); // عنوان استوری
            $table->text('description')->nullable(); // توضیحات استوری
            $table->enum('type', ['image', 'video'])->default('image'); // نوع محتوا: تصویر یا ویدیو
            $table->string('media_path'); // مسیر فایل (تصویر یا ویدیو)
            $table->string('thumbnail_path')->nullable(); // مسیر تصویر پیش‌نمایش برای ویدیو
            $table->enum('status', ['active', 'inactive', 'pending'])->default('active'); // وضعیت استوری
            $table->boolean('is_live')->default(false); // آیا زنده است یا نه
            $table->timestamp('live_start_time')->nullable(); // زمان شروع زنده
            $table->timestamp('live_end_time')->nullable(); // زمان پایان زنده
            $table->integer('duration')->nullable(); // مدت زمان ویدیو (ثانیه)
            $table->integer('views_count')->default(0); // تعداد بازدید
            $table->integer('likes_count')->default(0); // تعداد لایک
            $table->integer('order')->default(0); // ترتیب نمایش
            $table->json('metadata')->nullable(); // اطلاعات اضافی (مثل ابعاد فایل، حجم و...)

            // Foreign Keys
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade'); // کاربر ایجاد کننده
            $table->foreignId('doctor_id')->nullable()->constrained()->onDelete('cascade'); // پزشک
            $table->foreignId('medical_center_id')->nullable()->constrained()->onDelete('cascade'); // مرکز درمانی
            $table->foreignId('manager_id')->nullable()->constrained()->onDelete('cascade'); // مدیر

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'is_live']);
            $table->index(['user_id', 'status']);
            $table->index(['doctor_id', 'status']);
            $table->index(['medical_center_id', 'status']);
            $table->index(['manager_id', 'status']);
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stories');
    }
};
