<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('manual_appointments', function (Blueprint $table) {
            // اضافه کردن ستون‌های جدید
            

            // اصلاح ستون payment_method
            $table->enum('payment_method', ['online', 'cash', 'card_to_card', 'pos'])->nullable()->after('status');
            $table->enum('payment_status', ['pending', 'paid', 'unpaid'])->default('pending')->after('payment_method')->nullable();
            // اضافه کردن کلید خارجی برای insurance_id
        });
    }

    public function down()
    {
        Schema::table('manual_appointments', function (Blueprint $table) {
         
        });
    }
};
