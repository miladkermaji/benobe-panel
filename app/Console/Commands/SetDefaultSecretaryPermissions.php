<?php

namespace App\Console\Commands;

use App\Models\Secretary;
use App\Models\SecretaryPermission;
use Illuminate\Console\Command;

class SetDefaultSecretaryPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'secretary:set-default-permissions {--force : Force update existing permissions}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set default permissions for all secretaries';

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

        $secretaries = Secretary::all();
        $bar = $this->output->createProgressBar($secretaries->count());
        $bar->start();

        foreach ($secretaries as $secretary) {
            $permissionRecord = SecretaryPermission::where('secretary_id', $secretary->id)->first();

            if (!$permissionRecord) {
                // ایجاد دسترسی‌های جدید
                SecretaryPermission::create([
                    'secretary_id' => $secretary->id,
                    'doctor_id' => $secretary->doctor_id,
                    'medical_center_id' => $secretary->medical_center_id,
                    'permissions' => $defaultPermissions,
                    'has_access' => true
                ]);
                $this->info("Created permissions for secretary: {$secretary->first_name} {$secretary->last_name}");
            } elseif ($this->option('force')) {
                // به‌روزرسانی دسترسی‌های موجود
                $permissionRecord->update([
                    'permissions' => $defaultPermissions,
                    'has_access' => true
                ]);
                $this->info("Updated permissions for secretary: {$secretary->first_name} {$secretary->last_name}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Default permissions have been set for all secretaries!');
    }
}
