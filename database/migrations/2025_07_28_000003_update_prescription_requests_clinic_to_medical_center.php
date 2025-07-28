<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('prescription_requests', function (Blueprint $table) {
            // حذف کلید خارجی قدیمی
            $table->dropForeign(['clinic_id']);

            // تغییر نام ستون
            $table->renameColumn('clinic_id', 'medical_center_id');
        });

        Schema::table('prescription_requests', function (Blueprint $table) {
            // اضافه کردن کلید خارجی جدید
            $table->foreign('medical_center_id')
                ->references('id')
                ->on('medical_centers')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('prescription_requests', function (Blueprint $table) {
            // حذف کلید خارجی جدید
            $table->dropForeign(['medical_center_id']);

            // تغییر نام ستون به حالت قبلی
            $table->renameColumn('medical_center_id', 'clinic_id');
        });

        Schema::table('prescription_requests', function (Blueprint $table) {
            // اضافه کردن کلید خارجی قدیمی
            $table->foreign('clinic_id')
                ->references('id')
                ->on('clinics')
                ->onDelete('set null');
        });
    }
};
