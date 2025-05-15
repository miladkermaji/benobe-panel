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
        Schema::table('counseling_appointments', function (Blueprint $table) {

            $table->enum('payment_method', ['online', 'cash', 'card_to_card', 'pos'])->default('online')->after('payment_status')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('counseling_appointments', function (Blueprint $table) {
            
$table->dropColumn('payment_method');

        });
    }
};
