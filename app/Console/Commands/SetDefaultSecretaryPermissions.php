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
