<?php

namespace App\Traits;

use App\Models\Clinic;
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
        $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$user) {
            return null;
        }

        $doctorId = $user instanceof \App\Models\Doctor ? $user->id : $user->doctor_id;
        $clinicId = $this->getSelectedClinicId();

        if (!$clinicId) {
            return null;
        }

        return Clinic::where('doctor_id', $doctorId)
            ->where('id', $clinicId)
            ->first();
    }

    /**
     * Get the selected clinic ID for the current user
     *
     * @return int|null
     */
    public function getSelectedClinicId()
    {
        $user = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$user) {
            return null;
        }

        $doctorId = $user instanceof \App\Models\Doctor ? $user->id : $user->doctor_id;
        
        // اگر کاربر دکتر است، اولین مطب را برمی‌گرداند
        if ($user instanceof \App\Models\Doctor) {
            $clinic = Clinic::where('doctor_id', $doctorId)->first();
            return $clinic ? $clinic->id : null;
        }

        // اگر کاربر منشی است، مطب مربوط به منشی را برمی‌گرداند
        if ($user instanceof \App\Models\Secretary) {
            return $user->clinic_id;
        }

        return null;
    }
}
