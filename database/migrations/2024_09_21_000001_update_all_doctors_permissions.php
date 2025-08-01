<?php

use App\Models\Doctor;
use App\Models\DoctorPermission;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
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

        // اول همه دسترسی‌های موجود را پاک می‌کنیم
        DoctorPermission::truncate();

        // سپس برای همه پزشکان دسترسی‌های پیش‌فرض را اضافه می‌کنیم
        $doctors = Doctor::all();
        foreach ($doctors as $doctor) {
            DoctorPermission::create([
                'doctor_id' => $doctor->id,
                'permissions' => $defaultPermissions
            ]);
        }
    }

    public function down()
    {
        // در صورت نیاز به rollback، همه دسترسی‌ها را پاک می‌کنیم
        DoctorPermission::truncate();
    }
};
