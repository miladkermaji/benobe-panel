<?php

namespace App\Console\Commands;

use App\Models\MedicalCenter;
use App\Models\MedicalCenterPermission;
use Illuminate\Console\Command;

class SetDefaultMedicalCenterPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'medical-center:set-default-permissions {--force : Force update existing permissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set default permissions for all medical centers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $defaultPermissions = [
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
            "#",
            "workhours",
            "mc-workhours",
            "dashboard",
            "mc-panel",
            "mc.panel.doctors.edit",
            "mc.panel.profile.edit"
        ];

        $medicalCenters = MedicalCenter::all();
        $bar = $this->output->createProgressBar($medicalCenters->count());
        $bar->start();

        foreach ($medicalCenters as $medicalCenter) {
            $permissionRecord = MedicalCenterPermission::where('medical_center_id', $medicalCenter->id)->first();

            if (!$permissionRecord) {
                // ایجاد دسترسی‌های جدید
                MedicalCenterPermission::create([
                    'medical_center_id' => $medicalCenter->id,
                    'permissions' => $defaultPermissions
                ]);
                $this->info("Created permissions for medical center: {$medicalCenter->name}");
            } elseif ($this->option('force')) {
                // به‌روزرسانی دسترسی‌های موجود
                $permissionRecord->update([
                    'permissions' => $defaultPermissions
                ]);
                $this->info("Updated permissions for medical center: {$medicalCenter->name}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Default permissions have been set for all medical centers!');
    }
}
