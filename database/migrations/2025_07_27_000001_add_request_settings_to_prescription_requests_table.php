<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('prescription_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription_requests', 'request_enabled')) {
                $table->tinyInteger('request_enabled')->default(0)->after('transaction_id');
            }
            if (!Schema::hasColumn('prescription_requests', 'enabled_types')) {
                $table->json('enabled_types')->nullable()->after('request_enabled');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_requests', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_requests', 'request_enabled')) {
                $table->dropColumn('request_enabled');
            }
            if (Schema::hasColumn('prescription_requests', 'enabled_types')) {
                $table->dropColumn('enabled_types');
            }
        });
    }
};
