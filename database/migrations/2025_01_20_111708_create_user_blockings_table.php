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
        Schema::create('user_blockings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // کاربر مسدود شده
            $table->unsignedBigInteger('doctor_id')->nullable(); // دکتری که کاربر نزد او مسدود شده است
            $table->unsignedBigInteger('medical_center_id')->nullable(); // مرکز درمانی که کاربر نزد آن مسدود شده است

            $table->unsignedBigInteger('manager_id')->nullable(); // مدیری که کاربر رو مسدود کرده

            $table->dateTime('blocked_at'); // زمان شروع مسدودیت
            $table->dateTime('unblocked_at')->nullable(); // زمان پایان مسدودیت
            $table->string('reason')->nullable(); // دلیل مسدودیت
            $table->boolean('is_notified')->default(false); // آیا کاربر از مسدودیت مطلع شده؟
            $table->boolean('is_auto_unblocked')->default(false); // آیا مسدودیت به‌طور خودکار رفع شده؟
            $table->tinyInteger('status')->default(1)->comment('0 => unblocked 1 => blocked');
            $table->timestamps();

            // ایجاد کلیدهای خارجی
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->foreign('manager_id')->references('id')->on('managers')->onDelete('set null');

            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_blockings');
    }
};
