<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up()
 {
  Schema::create('transactions', function (Blueprint $table) {
   $table->id();
   $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
   $table->decimal('amount', 15, 2); // مبلغ تراکنش
   $table->string('gateway'); // نام درگاه پرداخت
   $table->string('status')->default('pending'); // وضعیت تراکنش (pending, paid, failed)
   $table->string('transaction_id')->nullable(); // شناسه تراکنش از درگاه
   $table->text('meta')->nullable(); // اطلاعات اضافی (مثلاً جزئیات درگاه)
   $table->timestamps();
  });
 }

 public function down()
 {
  Schema::dropIfExists('transactions');
 }
};
