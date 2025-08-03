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
        Schema::create('login_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // برای کاربران عادی
            $table->unsignedBigInteger('doctor_id')->nullable(); // برای دکترها
            $table->unsignedBigInteger('secretary_id')->nullable(); // برای منشی‌ها
            $table->unsignedBigInteger('manager_id')->nullable(); // برای منشی‌ها
            $table->unsignedBigInteger('medical_center_id')->nullable()->after('manager_id');
            $table->string('user_type'); // 'doctor', 'secretary', 'user'
            $table->timestamp('login_at')->nullable(); // زمان ورود
            $table->timestamp('logout_at')->nullable(); // زمان خروج
            $table->string('ip_address')->nullable(); // آی‌پی کاربر
            $table->string('device')->nullable(); // نام دستگاه
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_logs');
    }
};
