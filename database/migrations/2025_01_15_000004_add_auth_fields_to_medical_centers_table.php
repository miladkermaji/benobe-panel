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
            $table->string('password')->nullable()->after('recommendation_percentage');
            $table->boolean('static_password_enabled')->default(false)->after('password');
            $table->boolean('two_factor_secret_enabled')->default(false)->after('static_password_enabled');
            $table->timestamp('mobile_verified_at')->nullable()->after('two_factor_secret_enabled');
            $table->rememberToken()->after('mobile_verified_at');
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
