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
        Schema::create('notification_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notifications')->onDelete('cascade');
            $table->string('recipient_type')->nullable(); // برای انعطاف‌پذیری با جدول‌های مختلف (users, doctors, secretaries)
            $table->unsignedBigInteger('recipient_id')->nullable(); // Made nullable for single phone notifications
            $table->string('phone_number')->nullable(); // شماره تلفن برای ارسال تکی
            $table->boolean('is_read')->default(false); // آیا دیده شده
            $table->timestamp('read_at')->nullable(); // زمان دیده شدن
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_recipients');
    }
};
