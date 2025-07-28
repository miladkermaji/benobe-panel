<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('medical_centers', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_centers', 'prescription_tariff')) {
                $table->decimal('prescription_tariff', 10, 2)->nullable()->after('consultation_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('medical_centers', function (Blueprint $table) {
            if (Schema::hasColumn('medical_centers', 'prescription_tariff')) {
                $table->dropColumn('prescription_tariff');
            }
        });
    }
};
