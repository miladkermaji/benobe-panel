<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('medical_center_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('medical_center_id');
            $table->unsignedBigInteger('userable_id')->nullable();
            $table->string('userable_type')->nullable();
            $table->unsignedBigInteger('appointment_id')->nullable();
            $table->text('comment');
            $table->text('reply')->nullable();
            $table->boolean('status')->default(0); // 0 = غیرفعال، 1 = فعال
            $table->string('ip_address')->nullable();
            $table->enum('acquaintance', ['other', 'friend', 'social', 'ads'])->nullable();
            $table->tinyInteger('overall_score')->nullable(); // امتیاز کلی (1-5)
            $table->boolean('recommend_center')->nullable(); // پیشنهاد مرکز درمانی
            $table->tinyInteger('score_behavior')->nullable(); // امتیاز رفتار پرسنل
            $table->tinyInteger('score_cleanliness')->nullable(); // امتیاز نظافت
            $table->tinyInteger('score_equipment')->nullable(); // امتیاز تجهیزات
            $table->tinyInteger('score_receptionist')->nullable(); // امتیاز منشی
            $table->tinyInteger('score_environment')->nullable(); // امتیاز محیط
            $table->string('waiting_time')->nullable(); // زمان انتظار
            $table->string('visit_reason')->nullable(); // دلیل مراجعه
            $table->text('receptionist_comment')->nullable(); // نظر درباره منشی
            $table->text('experience_comment')->nullable(); // نظر درباره تجربه کلی
            $table->timestamps();

            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');
            $table->foreign('appointment_id')->references('id')->on('appointments')->onDelete('set null');
            $table->index(['userable_id', 'userable_type']);
            $table->index('medical_center_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_center_reviews');
    }
};
