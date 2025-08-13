<?php

namespace App\Observers;

use App\Models\MedicalCenter;
use App\Models\MedicalCenterPermission;

class MedicalCenterObserver
{
    /**
     * Handle the MedicalCenter "created" event.
     */
    public function created(MedicalCenter $medicalCenter): void
    {
        // ایجاد دسترسی‌های پیش‌فرض برای مرکز درمانی جدید
        $defaultPermissions = [
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

        MedicalCenterPermission::create([
            'medical_center_id' => $medicalCenter->id,
            'permissions' => $defaultPermissions
        ]);
    }

    /**
     * Handle the MedicalCenter "updated" event.
     */
    public function updated(MedicalCenter $medicalCenter): void
    {
        //
    }

    /**
     * Handle the MedicalCenter "deleted" event.
     */
    public function deleted(MedicalCenter $medicalCenter): void
    {
        // حذف دسترسی‌های مرکز درمانی
        MedicalCenterPermission::where('medical_center_id', $medicalCenter->id)->delete();
    }

    /**
     * Handle the MedicalCenter "restored" event.
     */
    public function restored(MedicalCenter $medicalCenter): void
    {
        //
    }

    /**
     * Handle the MedicalCenter "force deleted" event.
     */
    public function forceDeleted(MedicalCenter $medicalCenter): void
    {
        // حذف دسترسی‌های مرکز درمانی
        MedicalCenterPermission::where('medical_center_id', $medicalCenter->id)->delete();
    }
}
