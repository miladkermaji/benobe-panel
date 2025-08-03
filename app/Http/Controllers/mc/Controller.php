<?php

namespace App\Http\Controllers\Mc;

use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Models\Secretary;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Traits\HasSelectedClinic;
use App\Traits\HasSelectedDoctor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use ValidatesRequests;
    use HasSelectedClinic;
    use HasSelectedDoctor;

    /**
     * Get the authenticated doctor from any of the supported guards
     *
     * @return Doctor
     * @throws \Exception
     */
    protected function getAuthenticatedDoctor(): Doctor
    {
        // Check if user is authenticated through doctor guard
        $doctor = Auth::guard('doctor')->user();
        if ($doctor instanceof Doctor) {
            return $doctor;
        }

        // Check if user is authenticated through secretary guard
        $secretary = Auth::guard('secretary')->user();
        if ($secretary instanceof Secretary) {
            return $secretary->doctor;
        }

        // Check if user is authenticated through medical_center guard
        $medicalCenter = Auth::guard('medical_center')->user();
        if ($medicalCenter instanceof MedicalCenter) {
            $selectedDoctor = $this->getSelectedDoctor();
            if ($selectedDoctor) {
                return $selectedDoctor;
            }
            throw new \Exception('هیچ پزشکی برای این مرکز درمانی انتخاب نشده است.');
        }

        throw new \Exception('کاربر احراز هویت شده از نوع Doctor نیست یا وجود ندارد.');
    }
}
