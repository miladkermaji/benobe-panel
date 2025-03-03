<?php

namespace App\Providers;

use App\Models\Otp\Otp;
use App\Models\Dr\Clinic;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use App\Models\Admin\Dashboard\Cities\Zone;
use App\Http\View\Composers\LoginConfirmForm;
use App\Models\Admin\Dashboard\Specialty\Specialty;
use App\Http\Controllers\App\Auth\LoginRegisterController;

class AppServiceProvider extends ServiceProvider
{
  /**
   * Register any application services.
   */
  public function register(): void {}

  /**
   * Bootstrap any application services.
   */
  public function boot(): void
  {

    View::composer('dr.panel.layouts.master', function ($view) {
      $doctorId = Auth::guard('doctor')->id();
      $clinics = Clinic::where('doctor_id', $doctorId)->get();

      $view->with('clinics', $clinics);
    });


  
  }
}
