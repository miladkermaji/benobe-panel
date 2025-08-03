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
        Schema::table('login_sessions', function (Blueprint $table) {
            // اضافه کردن ستون‌های پولی مورفیک
            $table->string('sessionable_type')->nullable()->after('id');
            $table->unsignedBigInteger('sessionable_id')->nullable()->after('sessionable_type');

            // اضافه کردن ایندکس برای پولی مورفیک
            $table->index(['sessionable_type', 'sessionable_id']);
        });

        // انتقال داده‌های موجود به ساختار پولی مورفیک
        $this->migrateExistingData();

        // حذف ستون‌های قدیمی
        Schema::table('login_sessions', function (Blueprint $table) {
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
        Schema::table('login_sessions', function (Blueprint $table) {
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
        Schema::table('login_sessions', function (Blueprint $table) {
            $table->dropIndex(['sessionable_type', 'sessionable_id']);
            $table->dropColumn(['sessionable_type', 'sessionable_id']);
        });
    }

    /**
     * انتقال داده‌های موجود به ساختار پولی مورفیک
     */
    private function migrateExistingData(): void
    {
        $loginSessions = DB::table('login_sessions')->get();

        foreach ($loginSessions as $session) {
            $sessionableType = null;
            $sessionableId = null;

            if ($session->user_id) {
                $sessionableType = 'App\Models\User';
                $sessionableId = $session->user_id;
            } elseif ($session->doctor_id) {
                $sessionableType = 'App\Models\Doctor';
                $sessionableId = $session->doctor_id;
            } elseif ($session->secretary_id) {
                $sessionableType = 'App\Models\Secretary';
                $sessionableId = $session->secretary_id;
            } elseif ($session->manager_id) {
                $sessionableType = 'App\Models\Admin\Manager';
                $sessionableId = $session->manager_id;
            } elseif ($session->medical_center_id) {
                $sessionableType = 'App\Models\MedicalCenter';
                $sessionableId = $session->medical_center_id;
            }

            if ($sessionableType && $sessionableId) {
                DB::table('login_sessions')
                    ->where('id', $session->id)
                    ->update([
                        'sessionable_type' => $sessionableType,
                        'sessionable_id' => $sessionableId,
                    ]);
            }
        }
    }

    /**
     * انتقال داده‌ها به ساختار قدیمی
     */
    private function rollbackData(): void
    {
        $loginSessions = DB::table('login_sessions')->get();

        foreach ($loginSessions as $session) {
            $updateData = [
                'user_id' => null,
                'doctor_id' => null,
                'secretary_id' => null,
                'manager_id' => null,
                'medical_center_id' => null,
            ];

            switch ($session->sessionable_type) {
                case 'App\Models\User':
                    $updateData['user_id'] = $session->sessionable_id;
                    break;
                case 'App\Models\Doctor':
                    $updateData['doctor_id'] = $session->sessionable_id;
                    break;
                case 'App\Models\Secretary':
                    $updateData['secretary_id'] = $session->sessionable_id;
                    break;
                case 'App\Models\Admin\Manager':
                    $updateData['manager_id'] = $session->sessionable_id;
                    break;
                case 'App\Models\MedicalCenter':
                    $updateData['medical_center_id'] = $session->sessionable_id;
                    break;
            }

            DB::table('login_sessions')
                ->where('id', $session->id)
                ->update($updateData);
        }
    }
};
