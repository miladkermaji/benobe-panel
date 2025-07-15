<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('prescription_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('prescription_requests', 'type')) {
                $table->enum('type', ['renew_lab', 'renew_drug', 'renew_insulin', 'sonography', 'mri', 'other'])->nullable()->after('requestable_type');
            } else {
                $table->enum('type', ['renew_lab', 'renew_drug', 'renew_insulin', 'sonography', 'mri', 'other'])->nullable()->change();
            }
            if (!Schema::hasColumn('prescription_requests', 'description')) {
                $table->string('description', 80)->nullable()->change();
            } else {
                $table->string('description', 80)->nullable()->change();
            }
            if (!Schema::hasColumn('prescription_requests', 'tracking_code')) {
                $table->unsignedBigInteger('tracking_code')->nullable()->after('description');
            } else {
                $table->unsignedBigInteger('tracking_code')->nullable()->change();
            }
            if (!Schema::hasColumn('prescription_requests', 'prescription_insurance_id')) {
                $table->unsignedBigInteger('prescription_insurance_id')->nullable()->after('status');
                $table->foreign('prescription_insurance_id')->references('id')->on('prescription_insurances')->onDelete('set null');
            }
            if (Schema::hasColumn('prescription_requests', 'insurance_id')) {
                $table->dropForeign(['insurance_id']);
                $table->dropColumn('insurance_id');
            }
            if (!Schema::hasColumn('prescription_requests', 'clinic_id')) {
                $table->unsignedBigInteger('clinic_id')->nullable()->after('price');
                $table->foreign('clinic_id')->references('id')->on('clinics')->onDelete('set null');
            }
            if (!Schema::hasColumn('prescription_requests', 'transaction_id')) {
                $table->unsignedBigInteger('transaction_id')->nullable()->after('clinic_id');
                $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('prescription_requests', function (Blueprint $table) {
            if (Schema::hasColumn('prescription_requests', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('prescription_requests', 'description')) {
                $table->text('description')->nullable()->change();
            }
            if (Schema::hasColumn('prescription_requests', 'tracking_code')) {
                $table->dropColumn('tracking_code');
            }
            if (Schema::hasColumn('prescription_requests', 'prescription_insurance_id')) {
                $table->dropForeign(['prescription_insurance_id']);
                $table->dropColumn('prescription_insurance_id');
            }
            if (Schema::hasColumn('prescription_requests', 'clinic_id')) {
                $table->dropForeign(['clinic_id']);
                $table->dropColumn('clinic_id');
            }
            if (Schema::hasColumn('prescription_requests', 'transaction_id')) {
                $table->dropForeign(['transaction_id']);
                $table->dropColumn('transaction_id');
            }
        });
    }
};
