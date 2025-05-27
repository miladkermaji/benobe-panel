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
            "#"
        ];

        // Get all doctors who don't have permissions
        $doctors = Doctor::whereDoesntHave('permissions')->get();

        // Add default permissions for each doctor
        foreach ($doctors as $doctor) {
            DoctorPermission::create([
                'doctor_id' => $doctor->id,
                'permissions' => $defaultPermissions
            ]);
        }
    }

    public function down()
    {
        // No need to do anything in down() since we're not creating/dropping tables
    }
};
