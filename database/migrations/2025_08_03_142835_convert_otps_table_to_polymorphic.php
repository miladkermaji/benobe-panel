<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            // اضافه کردن ستون‌های پولی مورفیک
            $table->string('otpable_type')->nullable()->after('id');
            $table->unsignedBigInteger('otpable_id')->nullable()->after('otpable_type');

            // اضافه کردن ایندکس برای پولی مورفیک
            $table->index(['otpable_type', 'otpable_id']);
        });

        // انتقال داده‌های موجود به ساختار پولی مورفیک
        $this->migrateExistingData();

        // حذف ستون‌های قدیمی
        Schema::table('otps', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['secretary_id']);
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['medical_center_id']);

            $table->dropColumn(['user_id', 'doctor_id', 'secretary_id', 'manager_id', 'medical_center_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otps', function (Blueprint $table) {
            // اضافه کردن ستون‌های قدیمی
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->nullable()->constrained('doctors')->onDelete('cascade');
            $table->foreignId('secretary_id')->nullable()->constrained('secretaries')->onDelete('cascade');
            $table->foreignId('manager_id')->nullable()->constrained('managers')->onDelete('cascade');
            $table->foreignId('medical_center_id')->nullable()->constrained('medical_centers')->onDelete('cascade');
        });

        // انتقال داده‌ها به ساختار قدیمی
        $this->rollbackData();

        // حذف ستون‌های پولی مورفیک
        Schema::table('otps', function (Blueprint $table) {
            $table->dropIndex(['otpable_type', 'otpable_id']);
            $table->dropColumn(['otpable_type', 'otpable_id']);
        });
    }

    /**
     * انتقال داده‌های موجود به ساختار پولی مورفیک
     */
    private function migrateExistingData(): void
    {
        $otps = DB::table('otps')->get();

        foreach ($otps as $otp) {
            $otpableType = null;
            $otpableId = null;

            if ($otp->user_id) {
                $otpableType = 'App\Models\User';
                $otpableId = $otp->user_id;
            } elseif ($otp->doctor_id) {
                $otpableType = 'App\Models\Doctor';
                $otpableId = $otp->doctor_id;
            } elseif ($otp->secretary_id) {
                $otpableType = 'App\Models\Secretary';
                $otpableId = $otp->secretary_id;
            } elseif ($otp->manager_id) {
                $otpableType = 'App\Models\Admin\Manager';
                $otpableId = $otp->manager_id;
            } elseif ($otp->medical_center_id) {
                $otpableType = 'App\Models\MedicalCenter';
                $otpableId = $otp->medical_center_id;
            }

            if ($otpableType && $otpableId) {
                DB::table('otps')
                    ->where('id', $otp->id)
                    ->update([
                        'otpable_type' => $otpableType,
                        'otpable_id' => $otpableId,
                    ]);
            }
        }
    }

    /**
     * انتقال داده‌ها به ساختار قدیمی
     */
    private function rollbackData(): void
    {
        $otps = DB::table('otps')->get();

        foreach ($otps as $otp) {
            $updateData = [
                'user_id' => null,
                'doctor_id' => null,
                'secretary_id' => null,
                'manager_id' => null,
                'medical_center_id' => null,
            ];

            switch ($otp->otpable_type) {
                case 'App\Models\User':
                    $updateData['user_id'] = $otp->otpable_id;
                    break;
                case 'App\Models\Doctor':
                    $updateData['doctor_id'] = $otp->otpable_id;
                    break;
                case 'App\Models\Secretary':
                    $updateData['secretary_id'] = $otp->otpable_id;
                    break;
                case 'App\Models\Admin\Manager':
                    $updateData['manager_id'] = $otp->otpable_id;
                    break;
                case 'App\Models\MedicalCenter':
                    $updateData['medical_center_id'] = $otp->otpable_id;
                    break;
            }

            DB::table('otps')
                ->where('id', $otp->id)
                ->update($updateData);
        }
    }
};
