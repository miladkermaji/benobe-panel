<?php
namespace App\Providers;

use App\Models\Clinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        View::composer('dr.panel.layouts.master', function ($view) {
            $doctorId = Auth::guard('doctor')->id();
            $clinics  = Clinic::where('doctor_id', $doctorId)->get();

            $view->with('clinics', $clinics);
        });

    }
}
