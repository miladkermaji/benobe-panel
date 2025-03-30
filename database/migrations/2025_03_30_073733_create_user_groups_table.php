<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('user_groups', function (Blueprint $table) {
            $table->id(); // کلید اصلی
            $table->string('name')->unique(); // نام گروه، یکتا
            $table->text('description')->nullable(); // توضیحات، اختیاری
            $table->boolean('is_active')->default(true); // وضعیت فعال/غیرفعال
            $table->timestamps(); // created_at و updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_groups');
    }
};
