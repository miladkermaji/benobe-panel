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
        Schema::table('medical_centers', function (Blueprint $table) {
            if (!Schema::hasColumn('medical_centers', 'password')) {
                $table->string('password')->nullable()->after('recommendation_percentage');
            }
            if (!Schema::hasColumn('medical_centers', 'static_password_enabled')) {
                $table->boolean('static_password_enabled')->default(false)->after('password');
            }
            if (!Schema::hasColumn('medical_centers', 'two_factor_secret_enabled')) {
                $table->boolean('two_factor_secret_enabled')->default(false)->after('static_password_enabled');
            }
            if (!Schema::hasColumn('medical_centers', 'mobile_verified_at')) {
                $table->timestamp('mobile_verified_at')->nullable()->after('two_factor_secret_enabled');
            }
            if (!Schema::hasColumn('medical_centers', 'remember_token')) {
                $table->rememberToken()->after('mobile_verified_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('medical_centers', function (Blueprint $table) {
            $table->dropColumn([
                'password',
                'static_password_enabled',
                'two_factor_secret_enabled',
                'mobile_verified_at',
                'remember_token'
            ]);
        });
    }
};
