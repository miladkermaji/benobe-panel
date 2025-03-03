<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('membership_fees', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // نام بسته
            $table->integer('days'); // تعداد روز
            $table->decimal('price', 10, 2); // مبلغ
            $table->enum('user_type', ['doctor', 'normal'])->default('normal'); // مشخص کردن نوع کاربر
            $table->tinyInteger('status')->default('0');// تعداد جستجوها  

            $table->integer('sort')->default(1); // ترتیب نمایش
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_fees');
    }
};
