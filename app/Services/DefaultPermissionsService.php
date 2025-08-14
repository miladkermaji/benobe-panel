<?php

namespace App\Services;

use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Manager;
use App\Models\MedicalCenter;
use App\Models\DoctorPermission;
use App\Models\SecretaryPermission;
use App\Models\ManagerPermission;
use App\Models\MedicalCenterPermission;
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
     * دسترسی‌های پیش‌فرض برای مدیران
     */
    private array $managerDefaultPermissions = [
        "dashboard",
        "admin-panel",
        "user_management",
        "doctor_management",
        "secretary_management",
        "medical_center_management",
        "appointment_management",
        "financial_management",
        "reports",
        "settings",
        "permissions",
        "profile",
        "admin-edit-profile",
        "admin-edit-profile-security",
        "admin-edit-profile-upgrade",
        "statistics",
        "messages",
        "admin-panel-tickets",
        "#"
    ];

    /**
     * دسترسی‌های پیش‌فرض برای مراکز درمانی (از config در صورت موجود بودن)
     */
    private function getMedicalCenterDefaultPermissions(): array
    {
        $fromConfig = config('medical-center-permissions');
        if (is_array($fromConfig) && !empty($fromConfig)) {
            return $fromConfig;
        }
        return [
            'dashboard',
            'mc-panel',
            'medical_center_management',
            'mc.panel.doctors.index',
            'mc.panel.doctors.create',
            'mc.panel.doctors.edit',
            'mc.panel.specialties.index',
            'mc.panel.specialties.create',
            'mc.panel.specialties.edit',
            'mc.panel.services.index',
            'mc.panel.services.create',
            'mc.panel.services.edit',
            'mc.panel.insurances.index',
            'mc.panel.insurances.create',
            'mc.panel.insurances.edit',
            'mc.panel.profile.edit',
            'workhours',
            'mc-workhours',
            'appointments',
            'mc-appointments',
            'mc.panel.doctornotes.index',
            'mc-mySpecialDays',
            'mc-scheduleSetting',
            'mc-vacation',
            'mc-doctor-blocking-users.index',
            'prescriptions',
            'mc.panel.my-prescriptions',
            'mc.panel.my-prescriptions.settings',
            'consult',
            'mc-moshavere_setting',
            'mc-moshavere_waiting',
            'mc-mySpecialDays-counseling',
            'mc-consult-term.index',
            'doctor_services',
            'mc.panel.doctor-services.index',
            'electronic_prescription',
            'mc-prescription.index',
            'mc-providers.index',
            'mc-favorite.templates.index',
            'mc-templates.favorite.service.index',
            'mc-patient-records',
            'financial_reports',
            'mc.panel.financial-reports.index',
            'mc-payment-setting',
            'mc-wallet-charge',
            'patient_communication',
            'mc.panel.send-message',
            'secretary_management',
            'mc-secretary-management',
            'mc-secretary-permissions',
            'clinic_management',
            'mc-clinic-management',
            'mc.panel.clinics.medical-documents',
            'mc-doctors.clinic.deposit',
            'profile',
            'mc-edit-profile',
            'mc-edit-profile-security',
            'mc-edit-profile-upgrade',
            'mc-my-performance',
            'mc-subuser',
            'my-mc-appointments',
            'mc.panel.doctor-faqs.index',
            'statistics',
            'mc-my-performance-chart',
            'messages',
            'mc-panel-tickets',
            '#',
        ];
    }

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
                    'permissions' => $this->doctorDefaultPermissions,
                    'has_access' => true,
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
     * اعمال دسترسی‌های پیش‌فرض برای مرکز درمانی
     */
    public function applyDefaultPermissionsForMedicalCenter(MedicalCenter $medicalCenter): bool
    {
        try {
            $existing = MedicalCenterPermission::where('medical_center_id', $medicalCenter->id)->first();
            if ($existing) {
                Log::info('Medical center already has permissions', [
                    'medical_center_id' => $medicalCenter->id,
                    'name' => $medicalCenter->name,
                ]);
                return false;
            }

            $defaults = $this->getMedicalCenterDefaultPermissions();
            MedicalCenterPermission::create([
                'medical_center_id' => $medicalCenter->id,
                'permissions' => $defaults,
            ]);

            Log::info('Default permissions applied for medical center', [
                'medical_center_id' => $medicalCenter->id,
                'name' => $medicalCenter->name,
                'permissions_count' => count($defaults),
            ]);
            return true;
        } catch (\Exception $e) {
            Log::error('Error applying default permissions for medical center', [
                'medical_center_id' => $medicalCenter->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * اعمال دسترسی‌های پیش‌فرض برای مدیران
     */
    public function applyDefaultPermissionsForManager(Manager $manager): bool
    {
        try {
            $existingPermission = ManagerPermission::where('manager_id', $manager->id)->first();

            if (!$existingPermission) {
                // ایجاد دسترسی‌های پیش‌فرض
                ManagerPermission::create([
                    'manager_id' => $manager->id,
                    'permissions' => $this->managerDefaultPermissions,
                    'has_access' => true,
                ]);

                Log::info("Default permissions applied for manager", [
                    'manager_id' => $manager->id,
                    'manager_name' => $manager->first_name . ' ' . $manager->last_name,
                    'permissions_count' => count($this->managerDefaultPermissions)
                ]);

                return true;
            }

            Log::info("Manager already has permissions", [
                'manager_id' => $manager->id,
                'manager_name' => $manager->first_name . ' ' . $manager->last_name
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error("Error applying default permissions for manager", [
                'manager_id' => $manager->id,
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
        } elseif ($user instanceof MedicalCenter) {
            return $this->applyDefaultPermissionsForMedicalCenter($user);
        } elseif ($user instanceof Manager) {
            return $this->applyDefaultPermissionsForManager($user);
        }

        Log::warning("Unknown user type for applying default permissions", [
            'type' => is_object($user) ? get_class($user) : gettype($user)
        ]);
        return false;
    }
}
