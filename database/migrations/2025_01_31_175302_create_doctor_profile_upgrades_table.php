<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::create('doctor_profile_upgrades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade'); // ارتباط با جدول doctors
            $table->string('payment_reference')->unique(); // کد پیگیری پرداخت
            $table->enum('payment_status', ['pending', 'paid', 'failed'])->default('pending'); // وضعیت پرداخت
            $table->decimal('amount', 10, 2); // مبلغ پرداختی
            $table->integer('days')->default(90); // مدت زمان اعتبار
            $table->timestamp('paid_at')->nullable(); // تاریخ پرداخت
            $table->timestamp('expires_at')->nullable(); // تاریخ انقضا
            $table->timestamps();

            $table->softDeletes();

        });
    }

    public function down()
    {
        Schema::dropIfExists('doctor_profile_upgrades');
    }
};
