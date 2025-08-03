<?php

namespace App\Traits;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\MedicalCenterSelectedDoctor;
use Illuminate\Support\Facades\Auth;

trait HasSelectedDoctor
{
    /**
     * Get the selected doctor for the current medical center
     *
     * @return Doctor|null
     */
    public function getSelectedDoctor()
    {
        $medicalCenter = $this->getMedicalCenter();
        if (!$medicalCenter) {
            return null;
        }

        $selectedDoctor = $medicalCenter->selectedDoctor;
        if (!$selectedDoctor || is_null($selectedDoctor->doctor_id)) {
            return null; // عدم انتخاب پزشک
        }

        return $selectedDoctor->doctor; // پزشک انتخاب‌شده
    }

    /**
     * Get the selected doctor ID for the current medical center
     *
     * @return int|null
     */
    public function getSelectedDoctorId()
    {
        $medicalCenter = $this->getMedicalCenter();
        if (!$medicalCenter) {
            return null;
        }

        $selectedDoctor = $medicalCenter->selectedDoctor;
        if (!$selectedDoctor) {
            return null; // هیچ رکوردی در جدول وجود ندارد
        }

        return $selectedDoctor->doctor_id; // ID پزشک انتخاب‌شده یا null
    }

    /**
     * Get the authenticated medical center
     *
     * @return MedicalCenter|null
     */
    protected function getMedicalCenter()
    {
        $user = Auth::guard('medical_center')->user();
        if (!$user) {
            return null;
        }

        return $user instanceof \App\Models\MedicalCenter ? $user : null;
    }
}
