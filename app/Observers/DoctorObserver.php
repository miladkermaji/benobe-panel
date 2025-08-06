<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Models\DoctorPermission;

class DoctorObserver
{
    /**
     * Handle the Doctor "created" event.
     */
    public function created(Doctor $doctor): void
    {
        // بررسی اینکه آیا دسترسی‌های پیش‌فرض وجود دارند یا نه
        $permissionRecord = DoctorPermission::where('doctor_id', $doctor->id)->first();

        if (!$permissionRecord) {
            // ایجاد دسترسی‌های پیش‌فرض برای پزشک جدید
            $defaultPermissions = [
                "dashboard",
                "dr-workhours",
                "appointments",
                "dr-appointments",
                "dr.panel.doctornotes.index",
                "prescriptions",
                "dr.panel.my-prescriptions",
                "dr.panel.my-prescriptions.settings",
                "consult",
                "dr-moshavere_setting",
                "dr-moshavere_waiting",
                "dr-mySpecialDays-counseling",
                "consult-term.index",
                "doctor_services",
                "dr.panel.doctor-services.index",
                "electronic_prescription",
                "prescription.index",
                "providers.index",
                "favorite.templates.index",
                "templates.favorite.service.index",
                "dr-patient-records",
                "financial_reports",
                "dr.panel.financial-reports.index",
                "dr-payment-setting",
                "dr-wallet-charge",
                "patient_communication",
                "dr.panel.send-message",
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

            DoctorPermission::create([
                'doctor_id' => $doctor->id,
                'permissions' => $defaultPermissions
            ]);
        }
    }

    /**
     * Handle the Doctor "updated" event.
     */
    public function updated(Doctor $doctor): void
    {
        //
    }

    /**
     * Handle the Doctor "deleted" event.
     */
    public function deleted(Doctor $doctor): void
    {
        // حذف دسترسی‌های پزشک
        DoctorPermission::where('doctor_id', $doctor->id)->delete();
    }

    /**
     * Handle the Doctor "restored" event.
     */
    public function restored(Doctor $doctor): void
    {
        //
    }

    /**
     * Handle the Doctor "force deleted" event.
     */
    public function forceDeleted(Doctor $doctor): void
    {
        // حذف دسترسی‌های پزشک
        DoctorPermission::where('doctor_id', $doctor->id)->delete();
    }
}
