<?php

namespace App\Providers;

use App\Models\Appointment;
use App\Models\DoctorService;
use App\Models\MedicalCenter;
use App\Models\Secretary;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use App\Observers\AppointmentObserver;
use App\Observers\MedicalCenterObserver;
use App\Observers\SecretaryObserver;
use Illuminate\Support\ServiceProvider;
use App\Observers\DoctorServiceObserver;
use App\Models\Doctor;
use App\Observers\DoctorObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        View::composer('dr.panel.layouts.master', function ($view) {
            $medicalCenters = collect(); // فعلاً خالی
            $view->with('medicalCenters', $medicalCenters);
        });

        Appointment::observe(AppointmentObserver::class);

        DoctorService::observe(DoctorServiceObserver::class);

        Doctor::observe(DoctorObserver::class);

        MedicalCenter::observe(MedicalCenterObserver::class);

        Secretary::observe(SecretaryObserver::class);

    }
}
