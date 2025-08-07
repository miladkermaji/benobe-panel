<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Manager;
use App\Models\ManagerPermission;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // دسترسی‌های پیش‌فرض برای مدیران سطح 2
        $defaultPermissions = [
            "dashboard",
            "medical_centers",
            "support",
            "admin-panel",
            "user_management",
            "admin.panel.users.index",
            "admin.panel.user-groups.index",
            "admin.panel.user-blockings.index",
            "doctor_management",
            "admin.panel.doctors.index",
            "admin.panel.best-doctors.index",
            "admin.panel.doctor-documents.index",
            "admin.panel.doctor-specialties.index",
            "admin.panel.doctor-comments.index",
            "admin.panel.doctors.permissions",
            "membership",
            "admin.panel.user-subscriptions.index",
            "admin.panel.user-membership-plans.index",
            "admin.panel.user-appointment-fees.index",
            "patient_management",
            "admin.panel.users.index",
            "admin.panel.sub-users.index",
            "medical_centers",
            "admin.panel.hospitals.index",
            "admin.panel.laboratories.index",
            "admin.panel.clinics.index",
            "admin.panel.treatment-centers.index",
            "admin.panel.imaging-centers.index",
            "admin.panel.medical-centers.permissions",
            "service_management",
            "admin.panel.services.index",
            "admin.panel.doctor-services.index",
            "content_management",
            "admin.panel.blogs.index",
            "admin.panel.specialties.index",
            "admin.panel.zones.index",
            "admin.panel.reviews.index",
            "site_settings",
            "admin.panel.menus.index",
            "admin.panel.banner-texts.index",
            "admin.panel.footer-contents.index",
            "admin.panel.setting.index",
            "support",
            "admin.panel.tickets.index"
        ];

        // اضافه کردن دسترسی‌های پیش‌فرض به مدیران سطح 2 که هنوز دسترسی ندارند
        $managers = Manager::where('permission_level', 2)
            ->whereDoesntHave('permissions')
            ->get();

        foreach ($managers as $manager) {
            ManagerPermission::create([
                'manager_id' => $manager->id,
                'permissions' => $defaultPermissions
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف تمام دسترسی‌های پیش‌فرض
        ManagerPermission::where('permissions', 'like', '%dashboard%')
            ->where('permissions', 'like', '%medical_centers%')
            ->where('permissions', 'like', '%support%')
            ->delete();
    }
};
