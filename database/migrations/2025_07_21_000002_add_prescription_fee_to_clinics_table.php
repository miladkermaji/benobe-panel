<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (!Schema::hasColumn('clinics', 'prescription_fee')) {
                $table->decimal('prescription_fee', 10, 2)->nullable()->after('consultation_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('clinics', function (Blueprint $table) {
            if (Schema::hasColumn('clinics', 'prescription_fee')) {
                $table->dropColumn('prescription_fee');
            }
        });
    }
};
