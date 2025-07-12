<?php

use App\Models\Doctor;
use App\Models\DoctorPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // دسترسی‌های جدید که باید اضافه شوند
        $newPermissions = [
            "prescription",
            "dr-patient-records",
            "prescription.index",
            "providers.index",
            "favorite.templates.index",
            "templates.favorite.service.index",
            "patient_records",
            "dr-patient-records",
            "dr.panel.doctor-faqs.index"
        ];

        // برای همه پزشکان موجود، دسترسی‌های جدید را اضافه کن
        $doctors = Doctor::all();
        foreach ($doctors as $doctor) {
            $permissionRecord = DoctorPermission::where('doctor_id', $doctor->id)->first();

            if ($permissionRecord) {
                // دسترسی‌های موجود را بگیر
                $existingPermissions = $permissionRecord->permissions ?? [];

                // دسترسی‌های جدید را اضافه کن (بدون تکرار)
                $updatedPermissions = array_unique(array_merge($existingPermissions, $newPermissions));

                // به‌روزرسانی دسترسی‌ها
                $permissionRecord->update([
                    'permissions' => $updatedPermissions
                ]);
            } else {
                // اگر دسترسی‌ای وجود ندارد، ایجاد کن
                $defaultPermissions = [
                    "dashboard",
                    "dr-panel",
                    "appointments",
                    "dr-appointments",
                    "dr-workhours",
                    "dr.panel.doctornotes.index",
                    "dr-mySpecialDays",
                    "dr-manual_nobat_setting",
                    "dr-manual_nobat",
                    "dr-scheduleSetting",
                    "dr-vacation",
                    "doctor-blocking-users.index",
                    "consult",
                    "dr-moshavere_setting",
                    "dr-moshavere_waiting",
                    "dr.panel.doctornotes.index",
                    "dr-mySpecialDays-counseling",
                    "consult-term.index",
                    "insurance",
                    "dr.panel.doctor-services.index",
                    "financial_reports",
                    "dr.panel.financial-reports.index",
                    "dr-payment-setting",
                    "dr-wallet-charge",
                    "secretary_management",
                    "dr-secretary-management",
                    "clinic_management",
                    "dr-clinic-management",
                    "dr.panel.clinics.medical-documents",
                    "doctors.clinic.deposit",
                    "permissions",
                    "dr-secretary-permissions",
                    "profile",
                    "dr-edit-profile",
                    "dr-edit-profile-security",
                    "dr-edit-profile-upgrade",
                    "dr-my-performance",
                    "dr-subuser",
                    "my-dr-appointments",
                    "statistics",
                    "dr-my-performance-chart",
                    "messages",
                    "dr-panel-tickets",
                    "#",
                    "prescription",
                    "dr-patient-records",
                    "prescription.index",
                    "providers.index",
                    "favorite.templates.index",
                    "templates.favorite.service.index",
                    "patient_records",
                    "dr-patient-records",
                    "dr.panel.doctor-faqs.index"
                ];

                DoctorPermission::create([
                    'doctor_id' => $doctor->id,
                    'permissions' => $defaultPermissions
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // در صورت نیاز به rollback، دسترسی‌های جدید را حذف کن
        $doctors = Doctor::all();
        foreach ($doctors as $doctor) {
            $permissionRecord = DoctorPermission::where('doctor_id', $doctor->id)->first();

            if ($permissionRecord) {
                $existingPermissions = $permissionRecord->permissions ?? [];

                // دسترسی‌های جدید را حذف کن
                $permissionsToRemove = [
                    "prescription",
                    "dr-patient-records",
                    "prescription.index",
                    "providers.index",
                    "favorite.templates.index",
                    "templates.favorite.service.index",
                    "patient_records",
                    "dr-patient-records",
                    "dr.panel.doctor-faqs.index"
                ];

                $updatedPermissions = array_diff($existingPermissions, $permissionsToRemove);

                $permissionRecord->update([
                    'permissions' => array_values($updatedPermissions)
                ]);
            }
        }
    }
};
