<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        // Remove referral_code from prescription_insurances if exists
        if (Schema::hasColumn('prescription_insurances', 'referral_code')) {
            Schema::table('prescription_insurances', function (Blueprint $table) {
                $table->dropColumn('referral_code');
            });
        }
        // Add referral_code to prescription_requests if not exists
        if (!Schema::hasColumn('prescription_requests', 'referral_code')) {
            Schema::table('prescription_requests', function (Blueprint $table) {
                $table->string('referral_code')->nullable()->after('tracking_code');
            });
        }
    }
    public function down(): void
    {
        // Add referral_code back to prescription_insurances
        if (!Schema::hasColumn('prescription_insurances', 'referral_code')) {
            Schema::table('prescription_insurances', function (Blueprint $table) {
                $table->string('referral_code')->nullable();
            });
        }
        // Remove referral_code from prescription_requests
        if (Schema::hasColumn('prescription_requests', 'referral_code')) {
            Schema::table('prescription_requests', function (Blueprint $table) {
                $table->dropColumn('referral_code');
            });
        }
    }
};
