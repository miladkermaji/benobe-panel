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
        $tables = [
            'appointments',
            'counseling_appointments',
            'doctor_notes',
            'vacations',
            'order_visits',
            'best_doctors',
            'manual_appointments',
            'manual_appointment_settings',
            'consultations',
            'special_daily_schedules',
            'doctor_holidays',
            'doctor_counseling_holidays',
            'counseling_holidays',
            'counseling_daily_schedules',
            'user_blockings',
            'doctor_wallet_transactions',
            'doctor_services'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'clinic_id') && !Schema::hasColumn($table, 'medical_center_id')) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    // Try to drop foreign key if it exists
                    try {
                        $tableBlueprint->dropForeign(['clinic_id']);
                    } catch (\Exception $e) {
                        // Foreign key doesn't exist, continue
                    }

                    // Rename the column
                    $tableBlueprint->renameColumn('clinic_id', 'medical_center_id');
                });

                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    // Add new foreign key constraint
                    try {
                        $tableBlueprint->foreign('medical_center_id')
                            ->references('id')
                            ->on('medical_centers')
                            ->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key already exists or table doesn't exist, continue
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'appointments',
            'counseling_appointments',
            'doctor_notes',
            'vacations',
            'order_visits',
            'best_doctors',
            'manual_appointments',
            'manual_appointment_settings',
            'consultations',
            'doctor_work_schedules',
            'special_daily_schedules',
            'doctor_holidays',
            'doctor_counseling_holidays',
            'counseling_holidays',
            'counseling_daily_schedules',
            'user_blockings',
            'doctor_wallet_transactions',
            'doctor_services'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'medical_center_id')) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    // Try to drop foreign key if it exists
                    try {
                        $tableBlueprint->dropForeign(['medical_center_id']);
                    } catch (\Exception $e) {
                        // Foreign key doesn't exist, continue
                    }

                    // Rename the column back
                    $tableBlueprint->renameColumn('medical_center_id', 'clinic_id');
                });

                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    // Add old foreign key constraint
                    try {
                        $tableBlueprint->foreign('clinic_id')
                            ->references('id')
                            ->on('clinics')
                            ->onDelete('set null');
                    } catch (\Exception $e) {
                        // Foreign key already exists or table doesn't exist, continue
                    }
                });
            }
        }
    }
};
