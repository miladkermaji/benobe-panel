<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Add indexes for doctors table - only essential ones
        Schema::table('doctors', function (Blueprint $table) {
            // Check if indexes don't exist before adding
            if (!$this->indexExists('doctors', 'doctors_name_active_idx')) {
                $table->index(['first_name', 'last_name', 'is_active'], 'doctors_name_active_idx');
            }
        });

        // Add indexes for specialties table
        Schema::table('specialties', function (Blueprint $table) {
            if (!$this->indexExists('specialties', 'specialties_name_status_idx')) {
                $table->index(['name', 'status'], 'specialties_name_status_idx');
            }
        });

        // Add indexes for medical_centers table
        Schema::table('medical_centers', function (Blueprint $table) {
            if (!$this->indexExists('medical_centers', 'medical_centers_title_active_idx')) {
                $table->index(['title', 'is_active'], 'medical_centers_title_active_idx');
            }
        });

        // Add indexes for services table
        Schema::table('services', function (Blueprint $table) {
            if (!$this->indexExists('services', 'services_name_status_idx')) {
                $table->index(['name', 'status'], 'services_name_status_idx');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('doctors', function (Blueprint $table) {
            $table->dropIndexIfExists('doctors_name_active_idx');
        });

        Schema::table('specialties', function (Blueprint $table) {
            $table->dropIndexIfExists('specialties_name_status_idx');
        });

        Schema::table('medical_centers', function (Blueprint $table) {
            $table->dropIndexIfExists('medical_centers_title_active_idx');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndexIfExists('services_name_status_idx');
        });
    }

    /**
     * Check if an index exists
     */
    private function indexExists($table, $indexName)
    {
        $indexes = DB::select("SHOW INDEX FROM {$table}");
        foreach ($indexes as $index) {
            if ($index->Key_name === $indexName) {
                return true;
            }
        }
        return false;
    }
};
