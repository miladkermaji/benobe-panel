<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    public function up()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('patientable_id')->nullable()->after('doctor_id');
            $table->string('patientable_type')->nullable()->after('patientable_id');
        });

        // انتقال داده‌های قبلی
        DB::table('appointments')->whereNotNull('patient_id')->update([
            'patientable_id' => DB::raw('patient_id'),
            'patientable_type' => 'App\\Models\\User',
        ]);

        // حذف foreign key و ستون patient_id
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->dropColumn('patient_id');
        });

        // ایندکس morph
        Schema::table('appointments', function (Blueprint $table) {
            $table->index(['patientable_id', 'patientable_type']);
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->unsignedBigInteger('patient_id')->nullable()->after('doctor_id');
        });
        // بازگرداندن داده‌ها (فقط برای user)
        DB::table('appointments')->where('patientable_type', 'App\\Models\\User')->update([
            'patient_id' => DB::raw('patientable_id'),
        ]);
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex(['patientable_id', 'patientable_type']);
            $table->dropColumn(['patientable_id', 'patientable_type']);
        });
    }
};
