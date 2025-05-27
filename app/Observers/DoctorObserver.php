<?php

namespace App\Observers;

use App\Models\Doctor;
use App\Models\DoctorPermission;

class DoctorObserver
{
    public function created(Doctor $doctor)
    {
        // دسترسی‌های پیش‌فرض برای پزشک جدید
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
            "#"
        ];

        // ایجاد دسترسی‌های پیش‌فرض برای پزشک جدید
        DoctorPermission::create([
            'doctor_id' => $doctor->id,
            'permissions' => $defaultPermissions
        ]);
    }
}
