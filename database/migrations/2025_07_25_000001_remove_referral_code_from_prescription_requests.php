<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('prescription_requests', 'referral_code')) {
            Schema::table('prescription_requests', function (Blueprint $table) {
                $table->dropColumn('referral_code');
            });
        }
    }
    public function down(): void
    {
        Schema::table('prescription_requests', function (Blueprint $table) {
            $table->string('referral_code')->nullable();
        });
    }
};
