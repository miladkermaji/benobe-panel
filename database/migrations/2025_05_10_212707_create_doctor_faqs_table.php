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
        Schema::create('doctor_faqs', function (Blueprint $table) {
            $table->id(); // کلید اصلی
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade'); // کلید خارجی به جدول doctors
            $table->string('question', 255); // متن سوال (حداکثر 255 کاراکتر)
            $table->text('answer'); // پاسخ سوال (متن طولانی)
            $table->boolean('is_active')->default(true); // وضعیت فعال/غیرفعال
            $table->unsignedInteger('order')->default(0); // ترتیب نمایش
            $table->timestamps(); // فیلدهای created_at و updated_at

            // شاخص‌ها برای بهینه‌سازی جستجو
            $table->index(['doctor_id']);
            $table->index(['is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('doctor_faqs');
    }
};
