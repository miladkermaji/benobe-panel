<?php

use App\Models\MedicalCenter;
use App\Models\MedicalCenterPermission;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up()
    {
        $defaultPermissions = [
            "dashboard",
            "mc-panel",
            "medical_center_management",
            "mc.panel.doctors.index",
            "mc.panel.doctors.create",
            "mc.panel.doctors.edit",
            "mc.panel.specialties.index",
            "mc.panel.specialties.create",
            "mc.panel.specialties.edit",
            "mc.panel.services.index",
            "mc.panel.services.create",
            "mc.panel.services.edit",
            "mc.panel.insurances.index",
            "mc.panel.insurances.create",
            "mc.panel.insurances.edit",
            "mc.panel.profile.edit",
            "workhours",
            "mc-workhours",
            "appointments",
            "mc-appointments",
            "mc.panel.doctornotes.index",
            "mc-mySpecialDays",
            "mc-scheduleSetting",
            "mc-vacation",
            "mc-doctor-blocking-users.index",
            "prescriptions",
            "mc.panel.my-prescriptions",
            "mc.panel.my-prescriptions.settings",
            "consult",
            "mc-moshavere_setting",
            "mc-moshavere_waiting",
            "mc-mySpecialDays-counseling",
            "consult-term.index",
            "doctor_services",
            "mc.panel.doctor-services.index",
            "electronic_prescription",
            "prescription.index",
            "providers.index",
            "favorite.templates.index",
            "templates.favorite.service.index",
            "mc-patient-records",
            "financial_reports",
            "mc.panel.financial-reports.index",
            "mc-payment-setting",
            "mc-wallet-charge",
            "patient_communication",
            "mc.panel.send-message",
            "secretary_management",
            "mc-secretary-management",
            "mc-secretary-permissions",
            "clinic_management",
            "mc-clinic-management",
            "mc.panel.clinics.medical-documents",
            "mc-doctors.clinic.deposit",
            "profile",
            "mc-edit-profile",
            "mc-edit-profile-security",
            "mc-edit-profile-upgrade",
            "mc-my-performance",
            "mc-subuser",
            "my-mc-appointments",
            "mc.panel.doctor-faqs.index",
            "statistics",
            "mc-my-performance-chart",
            "messages",
            "mc-panel-tickets",
            "#"
        ];

        // Get all medical centers who don't have permissions
        $medicalCenters = MedicalCenter::whereDoesntHave('permissions')->get();

        // Add default permissions for each medical center
        foreach ($medicalCenters as $medicalCenter) {
            MedicalCenterPermission::create([
                'medical_center_id' => $medicalCenter->id,
                'permissions' => $defaultPermissions
            ]);
        }
    }

    public function down()
    {
        // No need to do anything in down() since we're not creating/dropping tables
    }
};
