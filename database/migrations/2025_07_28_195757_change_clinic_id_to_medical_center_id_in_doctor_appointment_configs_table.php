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
        Schema::table('doctor_appointment_configs', function (Blueprint $table) {
            // حذف فیلد قدیمی اگر وجود دارد
            if (Schema::hasColumn('doctor_appointment_configs', 'clinic_id')) {
                $table->dropColumn('clinic_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('doctor_appointment_configs', function (Blueprint $table) {
            // اضافه کردن فیلد قدیمی
            $table->unsignedBigInteger('clinic_id')->nullable()->after('doctor_id');
        });
    }
};
