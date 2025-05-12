<?php


namespace App\Observers;

use App\Models\DoctorService;
use Illuminate\Support\Facades\Cache;

class DoctorServiceObserver
{
    public function created(DoctorService $service)
    {
        $this->invalidateCache($service);
    }

    public function updated(DoctorService $service)
    {
        $this->invalidateCache($service);
    }

    public function deleted(DoctorService $service)
    {
        $this->invalidateCache($service);
    }

    protected function invalidateCache(DoctorService $service)
    {
        $doctorId = $service->doctor_id;
        $clinicId = $service->clinic_id ?? 'default';
        Cache::forget("insurances_doctor_{$doctorId}_clinic_{$clinicId}");
        Cache::forget("services_doctor_{$doctorId}_clinic_{$clinicId}_*");
    }
}
