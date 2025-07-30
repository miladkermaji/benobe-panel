<?php

namespace App\Traits;

use App\Models\Doctor;
use App\Models\DoctorSelectedMedicalCenter;
use Illuminate\Support\Facades\Auth;

trait HasSelectedClinic
{
    /**
     * Get the selected medical center for the current user
     *
     * @return MedicalCenter|null
     */
    public function getSelectedMedicalCenter()
    {
        $doctor = $this->getDoctor();
        if (!$doctor) {
            return null;
        }

        $selectedMedicalCenter = $doctor->selectedMedicalCenter;
        if (!$selectedMedicalCenter || is_null($selectedMedicalCenter->medical_center_id)) {
            return null; // مشاوره آنلاین یا عدم انتخاب مرکز درمانی
        }

        return $selectedMedicalCenter->medicalCenter; // مرکز درمانی انتخاب‌شده
    }

    /**
     * Get the selected medical center ID for the current user
     *
     * @return int|null
     */
    public function getSelectedMedicalCenterId()
    {
        $doctor = $this->getDoctor();
        if (!$doctor) {
            return null;
        }

        $selectedMedicalCenter = $doctor->selectedMedicalCenter;
        if (!$selectedMedicalCenter) {
            return null; // هیچ رکوردی در جدول وجود ندارد
        }

        return $selectedMedicalCenter->medical_center_id; // ID مرکز درمانی انتخاب‌شده یا null برای مشاوره آنلاین
    }

    /**
     * Get the authenticated doctor
     *
     * @return Doctor|null
     */
    protected function getDoctor()
    {
        $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$user) {
            return null;
        }

        return $user instanceof \App\Models\Doctor ? $user : $user->doctor;
    }
}
