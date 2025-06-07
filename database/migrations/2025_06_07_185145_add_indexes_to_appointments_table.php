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
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['doctor_id', 'appointment_date'], 'idx_doctor_date');
            $table->index(['doctor_id', 'clinic_id'], 'idx_doctor_clinic');
            $table->index(['doctor_id', 'status'], 'idx_doctor_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('idx_doctor_date');
            $table->dropIndex('idx_doctor_clinic');
            $table->dropIndex('idx_doctor_status');
        });
    }
};
