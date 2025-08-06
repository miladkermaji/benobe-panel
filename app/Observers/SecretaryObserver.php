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
                "secretary-workhours",
                "appointments",
                "secretary-appointments",
                "secretary.panel.doctornotes.index",
                "prescriptions",
                "secretary.panel.my-prescriptions",
                "secretary.panel.my-prescriptions.settings",
                "consult",
                "secretary-moshavere_setting",
                "secretary-moshavere_waiting",
                "secretary-mySpecialDays-counseling",
                "consult-term.index",
                "doctor_services",
                "secretary.panel.doctor-services.index",
                "electronic_prescription",
                "prescription.index",
                "providers.index",
                "favorite.templates.index",
                "templates.favorite.service.index",
                "secretary-patient-records",
                "financial_reports",
                "secretary.panel.financial-reports.index",
                "secretary-payment-setting",
                "secretary-wallet-charge",
                "patient_communication",
                "secretary.panel.send-message",
                "profile",
                "secretary-edit-profile",
                "secretary-edit-profile-security",
                "secretary-edit-profile-upgrade",
                "secretary-my-performance",
                "secretary-subuser",
                "my-secretary-appointments",
                "secretary.panel.doctor-faqs.index",
                "statistics",
                "secretary-my-performance-chart",
                "messages",
                "secretary-panel-tickets",
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
