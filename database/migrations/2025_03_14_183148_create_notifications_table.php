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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // عنوان اعلان
            $table->text('message'); // متن اعلان
            $table->enum('type', ['info', 'success', 'warning', 'error'])->default('info'); // نوع اعلان
            $table->enum('target_group', ['all', 'doctors', 'secretaries', 'patients'])->nullable(); // گروه هدف (اختیاری)
            $table->boolean('is_active')->default(true); // وضعیت فعال/غیرفعال
            $table->dateTime('start_at')->nullable(); // زمان شروع
            $table->dateTime('end_at')->nullable(); // زمان پایان
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null'); // کاربر ایجادکننده
            $table->timestamps();
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
