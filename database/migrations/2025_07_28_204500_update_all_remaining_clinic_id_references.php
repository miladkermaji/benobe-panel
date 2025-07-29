<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update all remaining tables that might have clinic_id
        $tables = [
            'doctor_work_schedules',
            'doctor_services',
            'user_blockings',
            'doctor_wallet_transactions',
            'counseling_daily_schedules',
            'doctor_counseling_holidays',
            'counseling_holidays',
            'special_daily_schedules',
            'doctor_holidays',
            'doctor_counseling_work_schedules',
            'secretary_permissions',
            'insurances'
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

        // Also update appointments table if not already done
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'clinic_id') && !Schema::hasColumn('appointments', 'medical_center_id')) {
            Schema::table('appointments', function (Blueprint $tableBlueprint) {
                try {
                    $tableBlueprint->dropForeign(['clinic_id']);
                } catch (\Exception $e) {
                    // Foreign key doesn't exist, continue
                }
                $tableBlueprint->renameColumn('clinic_id', 'medical_center_id');
            });

            Schema::table('appointments', function (Blueprint $tableBlueprint) {
                try {
                    $tableBlueprint->foreign('medical_center_id')
                        ->references('id')
                        ->on('medical_centers')
                        ->onDelete('set null');
                } catch (\Exception $e) {
                    // Foreign key already exists, continue
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'doctor_work_schedules',
            'doctor_services',
            'user_blockings',
            'doctor_wallet_transactions',
            'counseling_daily_schedules',
            'doctor_counseling_holidays',
            'counseling_holidays',
            'special_daily_schedules',
            'doctor_holidays',
            'doctor_counseling_work_schedules',
            'secretary_permissions',
            'insurances',
            'appointments'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'medical_center_id')) {
                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
                    try {
                        $tableBlueprint->dropForeign(['medical_center_id']);
                    } catch (\Exception $e) {
                        // Foreign key doesn't exist, continue
                    }
                    $tableBlueprint->renameColumn('medical_center_id', 'clinic_id');
                });

                Schema::table($table, function (Blueprint $tableBlueprint) use ($table) {
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
