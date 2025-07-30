<?php

use App\Models\Doctor;
use App\Models\DoctorPermission;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up()
    {
        // Get all doctors
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            // Get or create doctor permissions
            $permissionRecord = DoctorPermission::firstOrCreate(
                ['doctor_id' => $doctor->id],
                ['permissions' => []]
            );

            $currentPermissions = $permissionRecord->permissions ?? [];

            // Add clinic_management and related permissions if not exists
            $permissionsToAdd = [
                'clinic_management',
                'dr-clinic-management',
                'dr.panel.clinics.medical-documents',
                'doctors.clinic.deposit'
            ];

            $updatedPermissions = array_unique(array_merge($currentPermissions, $permissionsToAdd));

            $permissionRecord->update(['permissions' => $updatedPermissions]);
        }
    }

    public function down()
    {
        // Get all doctors
        $doctors = Doctor::all();

        foreach ($doctors as $doctor) {
            $permissionRecord = DoctorPermission::where('doctor_id', $doctor->id)->first();

            if ($permissionRecord) {
                $currentPermissions = $permissionRecord->permissions ?? [];

                // Remove clinic_management and related permissions
                $permissionsToRemove = [
                    'clinic_management',
                    'dr-clinic-management',
                    'dr.panel.clinics.medical-documents',
                    'doctors.clinic.deposit'
                ];

                $updatedPermissions = array_diff($currentPermissions, $permissionsToRemove);

                $permissionRecord->update(['permissions' => array_values($updatedPermissions)]);
            }
        }
    }
};
