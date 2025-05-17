<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('counseling_appointments', function (Blueprint $table) {
            $table->enum('settlement_status', ['pending', 'settled'])->default('pending')->nullable()->after('final_price');
        });
    }

    public function down(): void
    {
        Schema::table('counseling_appointments', function (Blueprint $table) {
            $table->dropColumn('settlement_status');
        });
    }
};
