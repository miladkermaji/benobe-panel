<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\DoctorPermission;
use App\Models\SecretaryPermission;
use Illuminate\Support\Facades\Log;

class DefaultPermissionsService
{
    /**
     * دسترسی‌های پیش‌فرض برای پزشکان
     */
    private array $doctorDefaultPermissions = [
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

    /**
     * دسترسی‌های پیش‌فرض برای منشی‌ها
     */
    private array $secretaryDefaultPermissions = [
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

    /**
     * اعمال دسترسی‌های پیش‌فرض برای پزشک
     */
    public function applyDefaultPermissionsForDoctor(Doctor $doctor): bool
    {
        try {
            // بررسی اینکه آیا دسترسی‌ای قبلاً وجود دارد یا نه
            $existingPermission = DoctorPermission::where('doctor_id', $doctor->id)->first();

            if (!$existingPermission) {
                // ایجاد دسترسی‌های پیش‌فرض
                DoctorPermission::create([
                    'doctor_id' => $doctor->id,
                    'permissions' => $this->doctorDefaultPermissions
                ]);

                Log::info("Default permissions applied for doctor", [
                    'doctor_id' => $doctor->id,
                    'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
                    'permissions_count' => count($this->doctorDefaultPermissions)
                ]);

                return true;
            }

            Log::info("Doctor already has permissions", [
                'doctor_id' => $doctor->id,
                'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Error applying default permissions for doctor", [
                'doctor_id' => $doctor->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * اعمال دسترسی‌های پیش‌فرض برای منشی
     */
    public function applyDefaultPermissionsForSecretary(Secretary $secretary): bool
    {
        try {
            // بررسی اینکه آیا دسترسی‌ای قبلاً وجود دارد یا نه
            $existingPermission = SecretaryPermission::where('secretary_id', $secretary->id)->first();

            if (!$existingPermission) {
                // ایجاد دسترسی‌های پیش‌فرض
                SecretaryPermission::create([
                    'secretary_id' => $secretary->id,
                    'doctor_id' => $secretary->doctor_id,
                    'medical_center_id' => $secretary->medical_center_id,
                    'permissions' => $this->secretaryDefaultPermissions,
                    'has_access' => true
                ]);

                Log::info("Default permissions applied for secretary", [
                    'secretary_id' => $secretary->id,
                    'secretary_name' => $secretary->first_name . ' ' . $secretary->last_name,
                    'doctor_id' => $secretary->doctor_id,
                    'medical_center_id' => $secretary->medical_center_id,
                    'permissions_count' => count($this->secretaryDefaultPermissions)
                ]);

                return true;
            }

            Log::info("Secretary already has permissions", [
                'secretary_id' => $secretary->id,
                'secretary_name' => $secretary->first_name . ' ' . $secretary->last_name
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Error applying default permissions for secretary", [
                'secretary_id' => $secretary->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * اعمال دسترسی‌های پیش‌فرض بر اساس نوع کاربر
     */
    public function applyDefaultPermissions($user): bool
    {
        if ($user instanceof Doctor) {
            return $this->applyDefaultPermissionsForDoctor($user);
        } elseif ($user instanceof Secretary) {
            return $this->applyDefaultPermissionsForSecretary($user);
        }

        Log::warning("Unknown user type for applying default permissions", [
            'user_type' => get_class($user),
            'user_id' => $user->id ?? 'unknown'
        ]);

        return false;
    }
}
