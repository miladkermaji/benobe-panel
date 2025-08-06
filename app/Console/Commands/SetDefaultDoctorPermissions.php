<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use App\Models\DoctorPermission;
use Illuminate\Console\Command;

class SetDefaultDoctorPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'doctor:set-default-permissions {--force : Force update existing permissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set default permissions for all doctors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
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

        $doctors = Doctor::all();
        $bar = $this->output->createProgressBar($doctors->count());
        $bar->start();

        foreach ($doctors as $doctor) {
            $permissionRecord = DoctorPermission::where('doctor_id', $doctor->id)->first();

            if (!$permissionRecord) {
                // ایجاد دسترسی‌های جدید
                DoctorPermission::create([
                    'doctor_id' => $doctor->id,
                    'permissions' => $defaultPermissions
                ]);
                $this->info("Created permissions for doctor: {$doctor->full_name}");
            } elseif ($this->option('force')) {
                // به‌روزرسانی دسترسی‌های موجود
                $permissionRecord->update([
                    'permissions' => $defaultPermissions
                ]);
                $this->info("Updated permissions for doctor: {$doctor->full_name}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Default permissions have been set for all doctors!');
    }
}
