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
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('doctor_id')->nullable(); // کلید خارجی برای دکتر (اختیاری)
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('identifier')->unique(); // شناسه یکتا برای قالب پیامک
            $table->string('title'); // عنوان پیامک
            $table->text('content'); // متن پیامک
            $table->string('type')->default('auto'); // نوع پیامک (auto یا manual)
            $table->string('recipient_type')->nullable(); // نوع گیرنده پیامک
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sms_templates');
    }
};
