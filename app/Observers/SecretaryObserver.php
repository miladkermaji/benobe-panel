<?php

namespace App\Observers;

use App\Models\Secretary;
use App\Models\SecretaryPermission;

class SecretaryObserver
{
    /**
     * Handle the Secretary "created" event.
     */
    public function created(Secretary $secretary): void
    {
        // بررسی اینکه آیا دسترسی‌های پیش‌فرض وجود دارند یا نه
        $permissionRecord = SecretaryPermission::where('secretary_id', $secretary->id)->first();

        if (!$permissionRecord) {
            // ایجاد دسترسی‌های پیش‌فرض برای منشی جدید
            $defaultPermissions = [
                "dashboard",
                "dr-workhours",
                "appointments",
                "dr-appointments",
                "dr.panel.doctornotes.index",
                "dr-mySpecialDays",
                "dr-manual_nobat_setting",
                "dr-manual_nobat",
                "dr-scheduleSetting",
                "dr-vacation",
                "doctor-blocking-users.index",
                "my-prescriptions",
                "dr.panel.my-prescriptions",
                "dr.panel.my-prescriptions.settings",
                "consult",
                "dr-moshavere_setting",
                "dr-moshavere_waiting",
                "dr-mySpecialDays-counseling",
                "consult-term.index",
                "insurance",
                "doctor_services",
                "dr.panel.doctor-services.index",
                "prescription",
                "electronic_prescription",
                "prescription.index",
                "providers.index",
                "favorite.templates.index",
                "templates.favorite.service.index",
                "dr-patient-records",
                "patient_records",
                "financial_reports",
                "dr.panel.financial-reports.index",
                "dr-payment-setting",
                "dr-wallet-charge",
                "secretary_management",
                "dr-secretary-management",
                "dr-secretary-permissions",
                "clinic_management",
                "dr-clinic-management",
                "dr.panel.clinics.medical-documents",
                "doctors.clinic.deposit",
                "permissions",
                "profile",
                "dr-edit-profile",
                "dr-edit-profile-security",
                "dr-edit-profile-upgrade",
                "dr-my-performance",
                "dr-subuser",
                "my-dr-appointments",
                "dr.panel.doctor-faqs.index",
                "statistics",
                "dr-my-performance-chart",
                "messages",
                "dr-panel-tickets",
                "#"
            ];

            SecretaryPermission::create([
                'secretary_id' => $secretary->id,
                'doctor_id' => $secretary->doctor_id,
                'medical_center_id' => $secretary->medical_center_id,
                'permissions' => $defaultPermissions,
                'has_access' => true
            ]);
        }
    }

    /**
     * Handle the Secretary "updated" event.
     */
    public function updated(Secretary $secretary): void
    {
        //
    }

    /**
     * Handle the Secretary "deleted" event.
     */
    public function deleted(Secretary $secretary): void
    {
        // حذف دسترسی‌های منشی
        SecretaryPermission::where('secretary_id', $secretary->id)->delete();
    }

    /**
     * Handle the Secretary "restored" event.
     */
    public function restored(Secretary $secretary): void
    {
        //
    }

    /**
     * Handle the Secretary "force deleted" event.
     */
    public function forceDeleted(Secretary $secretary): void
    {
        // حذف دسترسی‌های منشی
        SecretaryPermission::where('secretary_id', $secretary->id)->delete();
    }
}
