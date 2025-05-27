<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // عنوان اعلان
            $table->text('message'); // متن اعلان
            $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info'); // نوع اعلان
            $table->enum('target_group', ['all', 'doctors', 'secretaries', 'patients'])->nullable(); // گروه هدف (اختیاری)
            $table->boolean('is_active')->default(true); // وضعیت فعال/غیرفعال
            $table->dateTime('start_at')->nullable(); // زمان شروع
            $table->dateTime('end_at')->nullable(); // زمان پایان
            $table->foreignId('created_by')->nullable()->constrained('managers')->onDelete('set null'); // کاربر ایجادکننده
            $table->timestamps();

            // اضافه کردن ایندکس‌ها
            $table->index('type');
            $table->index('target_group');
            $table->index('is_active');
            $table->index('start_at');
            $table->index('end_at');
            $table->index(['type', 'is_active']);
            $table->index(['target_group', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
