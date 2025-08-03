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
        Schema::table('login_attempts', function (Blueprint $table) {
            // اضافه کردن ستون‌های پولی مورفیک
            $table->string('attemptable_type')->nullable()->after('id');
            $table->unsignedBigInteger('attemptable_id')->nullable()->after('attemptable_type');

            // اضافه کردن ایندکس برای پولی مورفیک
            $table->index(['attemptable_type', 'attemptable_id']);
        });

        // انتقال داده‌های موجود به ساختار پولی مورفیک
        $this->migrateExistingData();

        // حذف ستون‌های قدیمی
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropForeign(['doctor_id']);
            $table->dropForeign(['secratary_id']);
            $table->dropForeign(['user_id']);
            $table->dropForeign(['manager_id']);
            $table->dropForeign(['medical_center_id']);

            $table->dropColumn(['doctor_id', 'secratary_id', 'user_id', 'manager_id', 'medical_center_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            // اضافه کردن ستون‌های قدیمی
            $table->unsignedBigInteger('doctor_id')->nullable();
            $table->unsignedBigInteger('secratary_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->unsignedBigInteger('medical_center_id')->nullable();

            // اضافه کردن foreign keys
            $table->foreign('doctor_id')->references('id')->on('doctors')->onDelete('cascade');
            $table->foreign('secratary_id')->references('id')->on('secretaries')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('manager_id')->references('id')->on('managers')->onDelete('cascade');
            $table->foreign('medical_center_id')->references('id')->on('medical_centers')->onDelete('cascade');
        });

        // انتقال داده‌ها به ساختار قدیمی
        $this->rollbackData();

        // حذف ستون‌های پولی مورفیک
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropIndex(['attemptable_type', 'attemptable_id']);
            $table->dropColumn(['attemptable_type', 'attemptable_id']);
        });
    }

    /**
     * انتقال داده‌های موجود به ساختار پولی مورفیک
     */
    private function migrateExistingData(): void
    {
        $loginAttempts = DB::table('login_attempts')->get();

        foreach ($loginAttempts as $attempt) {
            $attemptableType = null;
            $attemptableId = null;

            if ($attempt->user_id) {
                $attemptableType = 'App\Models\User';
                $attemptableId = $attempt->user_id;
            } elseif ($attempt->doctor_id) {
                $attemptableType = 'App\Models\Doctor';
                $attemptableId = $attempt->doctor_id;
            } elseif ($attempt->secratary_id) {
                $attemptableType = 'App\Models\Secretary';
                $attemptableId = $attempt->secratary_id;
            } elseif ($attempt->manager_id) {
                $attemptableType = 'App\Models\Admin\Manager';
                $attemptableId = $attempt->manager_id;
            } elseif ($attempt->medical_center_id) {
                $attemptableType = 'App\Models\MedicalCenter';
                $attemptableId = $attempt->medical_center_id;
            }

            if ($attemptableType && $attemptableId) {
                DB::table('login_attempts')
                    ->where('id', $attempt->id)
                    ->update([
                        'attemptable_type' => $attemptableType,
                        'attemptable_id' => $attemptableId,
                    ]);
            }
        }
    }

    /**
     * انتقال داده‌ها به ساختار قدیمی
     */
    private function rollbackData(): void
    {
        $loginAttempts = DB::table('login_attempts')->get();

        foreach ($loginAttempts as $attempt) {
            $updateData = [
                'user_id' => null,
                'doctor_id' => null,
                'secratary_id' => null,
                'manager_id' => null,
                'medical_center_id' => null,
            ];

            switch ($attempt->attemptable_type) {
                case 'App\Models\User':
                    $updateData['user_id'] = $attempt->attemptable_id;
                    break;
                case 'App\Models\Doctor':
                    $updateData['doctor_id'] = $attempt->attemptable_id;
                    break;
                case 'App\Models\Secretary':
                    $updateData['secratary_id'] = $attempt->attemptable_id;
                    break;
                case 'App\Models\Admin\Manager':
                    $updateData['manager_id'] = $attempt->attemptable_id;
                    break;
                case 'App\Models\MedicalCenter':
                    $updateData['medical_center_id'] = $attempt->attemptable_id;
                    break;
            }

            DB::table('login_attempts')
                ->where('id', $attempt->id)
                ->update($updateData);
        }
    }
};
