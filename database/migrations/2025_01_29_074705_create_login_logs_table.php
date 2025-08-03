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
            $table->morphs('loggable'); // Creates loggable_type and loggable_id columns
            $table->string('user_type'); // 'doctor', 'secretary', 'user', 'manager', 'medical_center'
            $table->timestamp('login_at')->nullable(); // زمان ورود
            $table->timestamp('logout_at')->nullable(); // زمان خروج
            $table->string('ip_address')->nullable(); // آی‌پی کاربر
            $table->string('device')->nullable(); // نام دستگاه
            $table->string('login_method')->nullable(); // روش ورود (otp, password, etc.)
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
