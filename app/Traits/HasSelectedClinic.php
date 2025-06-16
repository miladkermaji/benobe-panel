<?php

namespace App\Traits;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorSelectedClinic;
use Illuminate\Support\Facades\Auth;

trait HasSelectedClinic
{
    /**
     * Get the selected clinic for the current user
     *
     * @return Clinic|null
     */
    public function getSelectedClinic()
    {
        $doctor = $this->getDoctor();
        if (!$doctor) {
            return null;
        }

        $selectedClinic = $doctor->selectedClinic;
        if (!$selectedClinic || is_null($selectedClinic->clinic_id)) {
            return null; // مشاوره آنلاین یا عدم انتخاب کلینیک
        }

        return $selectedClinic->clinic; // کلینیک انتخاب‌شده
    }

    /**
     * Get the selected clinic ID for the current user
     *
     * @return int|null
     */
    public function getSelectedClinicId()
    {
        $doctor = $this->getDoctor();
        if (!$doctor) {
            return null;
        }

        return $doctor->selectedClinic?->clinic_id; // ID کلینیک انتخاب‌شده یا null برای مشاوره آنلاین
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
