<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentGatewaysTable extends Migration
{
    public function up()
    {
        Schema::create('payment_gateways', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // اسم درگاه (مثل zarinpal, idpay, etc)
            $table->string('title'); // نام نمایشی درگاه (مثل "زرین پال")
            $table->string('logo')->nullable(); // آدرس لوگوی درگاه
            $table->boolean('is_active')->default(false); // وضعیت فعال یا غیرفعال بودن
            $table->json('settings')->nullable(); // تنظیمات خاص هر درگاه (مثل merchant_id یا api_key)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_gateways');
    }
}