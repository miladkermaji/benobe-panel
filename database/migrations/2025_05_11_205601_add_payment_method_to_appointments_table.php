<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->enum('payment_method', ['online', 'cash', 'card_to_card', 'pos'])->default('online')->after('payment_status')->nullable();
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
