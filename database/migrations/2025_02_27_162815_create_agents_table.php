<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('agents', function (Blueprint $table) {
            $table->id();
            $table->string('full_name'); // نام و نام خانوادگی
            $table->string('mobile', 11)->unique(); // موبایل (11 رقم)
            $table->string('national_code', 10)->unique(); // کد ملی (10 رقم)
            $table->string('province'); // استان
            $table->string('city'); // شهر
            $table->boolean('status')->default(1); // وضعیت (فعال/غیرفعال)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agents');
    }
};