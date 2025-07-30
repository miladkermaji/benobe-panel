<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\DoctorMedicalCenter;
use Illuminate\Database\Seeder;

class DoctorMedicalCenterSeeder extends Seeder
{
    public function run(): void
    {
        // دریافت اولین دکتر و مرکز درمانی
        $doctor = Doctor::first();
        $medicalCenter = MedicalCenter::where('type', 'policlinic')->first();

        if ($doctor && $medicalCenter) {
            // بررسی اینکه آیا رابطه قبلاً وجود دارد یا نه
            $existingRelation = DoctorMedicalCenter::where('doctor_id', $doctor->id)
                ->where('medical_center_id', $medicalCenter->id)
                ->first();

            if (!$existingRelation) {
                DoctorMedicalCenter::create([
                    'doctor_id' => $doctor->id,
                    'medical_center_id' => $medicalCenter->id,
                ]);
            }

            $this->command->info("Relation created between Doctor ID: {$doctor->id} and Medical Center ID: {$medicalCenter->id}");
        } else {
            $this->command->error('Doctor or Medical Center not found!');
        }
    }
}
