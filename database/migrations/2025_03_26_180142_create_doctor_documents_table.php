<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('doctor_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id'); // شناسه پزشک
            $table->string('file_path'); // مسیر فایل (می‌تونه عکس، PDF، Word و غیره باشه)
            $table->string('file_type'); // نوع فایل (مثل image, pdf, docx)
            $table->string('title')->nullable(); // عنوان یا توضیح کوتاه برای مدرک
            $table->boolean('is_verified')->default(false); // وضعیت تأیید مدرک (در صورت نیاز به تأیید توسط ادمین)
            $table->timestamps();

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_documents');
    }
};
