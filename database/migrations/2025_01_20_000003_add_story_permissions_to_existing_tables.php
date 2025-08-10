 <?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // اضافه کردن دسترسی‌های استوری به جدول دسترسی‌های پزشک
        $this->addStoryPermissionsToDoctorPermissions();

        // اضافه کردن دسترسی‌های استوری به جدول دسترسی‌های مرکز درمانی
        $this->addStoryPermissionsToMedicalCenterPermissions();

        // اضافه کردن دسترسی‌های استوری به جدول دسترسی‌های مدیر
        $this->addStoryPermissionsToManagerPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف دسترسی‌های استوری از جدول دسترسی‌های پزشک
        $this->removeStoryPermissionsFromDoctorPermissions();

        // حذف دسترسی‌های استوری از جدول دسترسی‌های مرکز درمانی
        $this->removeStoryPermissionsFromMedicalCenterPermissions();

        // حذف دسترسی‌های استوری از جدول دسترسی‌های مدیر
        $this->removeStoryPermissionsFromManagerPermissions();
    }

    /**
     * اضافه کردن دسترسی‌های استوری به دسترسی‌های پزشک
     */
    private function addStoryPermissionsToDoctorPermissions()
    {
        $storyPermissions = [
            'stories',
            'dr.panel.stories.index',
            'dr.panel.stories.create',
            'dr.panel.stories.edit',
            'dr.panel.stories.destroy',
            'dr.panel.stories.live',
            'dr.panel.stories.upload',
        ];

        DB::table('doctor_permissions')->orderBy('id')->chunk(100, function ($permissions) use ($storyPermissions) {
            foreach ($permissions as $permission) {
                $currentPermissions = json_decode($permission->permissions, true) ?: [];
                $newPermissions = array_merge($currentPermissions, $storyPermissions);

                DB::table('doctor_permissions')
                    ->where('id', $permission->id)
                    ->update(['permissions' => json_encode($newPermissions)]);
            }
        });
    }

    /**
     * اضافه کردن دسترسی‌های استوری به دسترسی‌های مرکز درمانی
     */
    private function addStoryPermissionsToMedicalCenterPermissions()
    {
        $storyPermissions = [
            'stories',
            'mc.panel.stories.index',
            'mc.panel.stories.create',
            'mc.panel.stories.edit',
            'mc.panel.stories.destroy',
            'mc.panel.stories.live',
            'mc.panel.stories.upload',
        ];

        DB::table('medical_center_permissions')->orderBy('id')->chunk(100, function ($permissions) use ($storyPermissions) {
            foreach ($permissions as $permission) {
                $currentPermissions = json_decode($permission->permissions, true) ?: [];
                $newPermissions = array_merge($currentPermissions, $storyPermissions);

                DB::table('medical_center_permissions')
                    ->where('id', $permission->id)
                    ->update(['permissions' => json_encode($newPermissions)]);
            }
        });
    }

    /**
     * اضافه کردن دسترسی‌های استوری به دسترسی‌های مدیر
     */
    private function addStoryPermissionsToManagerPermissions()
    {
        $storyPermissions = [
            'stories',
            'admin.panel.stories.index',
            'admin.panel.stories.create',
            'admin.panel.stories.edit',
            'admin.panel.stories.destroy',
            'admin.panel.stories.approve',
            'admin.panel.stories.reject',
            'admin.panel.stories.analytics',
        ];

        DB::table('manager_permissions')->orderBy('id')->chunk(100, function ($permissions) use ($storyPermissions) {
            foreach ($permissions as $permission) {
                $currentPermissions = json_decode($permission->permissions, true) ?: [];
                $newPermissions = array_merge($currentPermissions, $storyPermissions);

                DB::table('manager_permissions')
                    ->where('id', $permission->id)
                    ->update(['permissions' => json_encode($newPermissions)]);
            }
        });
    }

    /**
     * حذف دسترسی‌های استوری از دسترسی‌های پزشک
     */
    private function removeStoryPermissionsFromDoctorPermissions()
    {
        $storyPermissions = [
            'stories',
            'dr.panel.stories.index',
            'dr.panel.stories.create',
            'dr.panel.stories.edit',
            'dr.panel.stories.destroy',
            'dr.panel.stories.live',
            'dr.panel.stories.upload',
        ];

        DB::table('doctor_permissions')->orderBy('id')->chunk(100, function ($permissions) use ($storyPermissions) {
            foreach ($permissions as $permission) {
                $currentPermissions = json_decode($permission->permissions, true) ?: [];
                $newPermissions = array_diff($currentPermissions, $storyPermissions);

                DB::table('doctor_permissions')
                    ->where('id', $permission->id)
                    ->update(['permissions' => json_encode(array_values($newPermissions))]);
            }
        });
    }

    /**
     * حذف دسترسی‌های استوری از دسترسی‌های مرکز درمانی
     */
    private function removeStoryPermissionsFromMedicalCenterPermissions()
    {
        $storyPermissions = [
            'stories',
            'mc.panel.stories.index',
            'mc.panel.stories.create',
            'mc.panel.stories.edit',
            'mc.panel.stories.destroy',
            'mc.panel.stories.live',
            'mc.panel.stories.upload',
        ];

        DB::table('medical_center_permissions')->orderBy('id')->chunk(100, function ($permissions) use ($storyPermissions) {
            foreach ($permissions as $permission) {
                $currentPermissions = json_decode($permission->permissions, true) ?: [];
                $newPermissions = array_diff($currentPermissions, $storyPermissions);

                DB::table('medical_center_permissions')
                    ->where('id', $permission->id)
                    ->update(['permissions' => json_encode(array_values($newPermissions))]);
            }
        });
    }

    /**
     * حذف دسترسی‌های استوری از دسترسی‌های مدیر
     */
    private function removeStoryPermissionsFromManagerPermissions()
    {
        $storyPermissions = [
            'stories',
            'admin.panel.stories.index',
            'admin.panel.stories.create',
            'admin.panel.stories.edit',
            'admin.panel.stories.destroy',
            'admin.panel.stories.approve',
            'admin.panel.stories.reject',
            'admin.panel.stories.analytics',
        ];

        DB::table('manager_permissions')->orderBy('id')->chunk(100, function ($permissions) use ($storyPermissions) {
            foreach ($permissions as $permission) {
                $currentPermissions = json_decode($permission->permissions, true) ?: [];
                $newPermissions = array_diff($currentPermissions, $storyPermissions);

                DB::table('manager_permissions')
                    ->where('id', $permission->id)
                    ->update(['permissions' => json_encode(array_values($newPermissions))]);
            }
        });
    }
};
