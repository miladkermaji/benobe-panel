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
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->unsignedBigInteger('medical_center_id')->nullable()->after('manager_id');

            // اضافه کردن foreign key
            $table->foreign('medical_center_id')
                ->references('id')
                ->on('medical_centers')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropForeign(['medical_center_id']);
            $table->dropColumn('medical_center_id');
        });
    }
};
