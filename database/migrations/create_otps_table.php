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
        Schema::create('otps', function (Blueprint $table) {
            $table->id();
            $table->string('otpable_type')->nullable();
            $table->unsignedBigInteger('otpable_id')->nullable();
            $table->string('token');
            $table->string('otp_code');
            $table->string('login_id')->comment('email address or mobile number');
            $table->tinyInteger('type')->default(0)->comment('0 => mobile number , 1 => email');
            $table->tinyInteger('used')->default(0)->comment('0 => not used , 1 => used');
            $table->tinyInteger('status')->default(0);
            $table->timestamps();

            // اضافه کردن ایندکس برای پولی مورفیک
            $table->index(['otpable_type', 'otpable_id']);

            // اضافه کردن ایندکس‌ها
            $table->index('type');
            $table->index('used');
            $table->index('status');
            $table->index('otp_code');
            $table->index(['login_id', 'type']);
            $table->index(['type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
