<?php

namespace App\Console\Commands;

use App\Models\Doctor;
use App\Models\DoctorPermission;
use Illuminate\Console\Command;

class AddDefaultDoctorPermissions extends Command
{
    protected $signature = 'doctors:add-default-permissions';
    protected $description = 'Add default permissions to all existing doctors';

    public function handle()
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

        $doctors = Doctor::all();
        $bar = $this->output->createProgressBar(count($doctors));

        foreach ($doctors as $doctor) {
            // اگر دسترسی‌ای برای این پزشک وجود ندارد، ایجاد کن
            if (!$doctor->permissions) {
                DoctorPermission::create([
                    'doctor_id' => $doctor->id,
                    'permissions' => $defaultPermissions
                ]);
            }
            $bar->advance();
        }

        $bar->finish();
        $this->info("\nتمام دسترسی‌های پیش‌فرض به همه پزشکان اضافه شد.");
    }
}
