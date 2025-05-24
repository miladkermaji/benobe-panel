<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('best_doctors', function (Blueprint $table) {
            $table->renameColumn('hospital_id', 'clinic_id');
        });
    }

    public function down(): void
    {
        Schema::table('best_doctors', function (Blueprint $table) {
            $table->renameColumn('clinic_id', 'hospital_id');
        });
    }
};
