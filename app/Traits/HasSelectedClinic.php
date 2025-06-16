<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasSelectedClinic
{
    public function getSelectedClinic()
    {
        $doctor = Auth::guard('doctor')->check()
            ? Auth::guard('doctor')->user()
            : (Auth::guard('secretary')->check() ? Auth::guard('secretary')->user()->doctor : null);

        return $doctor ? $doctor->currentClinic : null;
    }

    public function getSelectedClinicId()
    {
        return $this->getSelectedClinic()?->id;
    }
}
