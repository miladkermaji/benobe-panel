<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('login_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('sessionable_type')->nullable();
            $table->unsignedBigInteger('sessionable_id')->nullable();
            $table->string('token', 60)->unique()->index();
            $table->unsignedTinyInteger('step')->default(1); // 1: ورود موبایل، 2: OTP، 3: رمز عبور، 4: دو عاملی
            $table->timestamp('expires_at');
            $table->timestamps();

            // اضافه کردن ایندکس برای پولی مورفیک
            $table->index(['sessionable_type', 'sessionable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_sessions');
    }
};
