<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('manual_appointments', function (Blueprint $table) {
            // فیلد نوع پرداخت (روش پرداخت)
            $table->enum('payment_method', ['cash', 'online', 'card', 'insurance'])->nullable()->after('status');

            // فیلد هزینه پایه
            $table->decimal('fee', 8, 2)->nullable()->after('payment_method');

            // فیلد قیمت نهایی
            $table->decimal('final_price', 14, 2)->nullable()->after('fee');
        });
    }

    public function down()
    {
        Schema::table('manual_appointments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'fee', 'final_price']);
        });
    }
};
