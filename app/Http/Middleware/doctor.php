<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class doctor
{
    protected $table = "doctors";

    public function handle(Request $request, Closure $next): Response
    {
        // چک کردن گارد مدیر (manager)
        if (Auth::guard('manager')->check()) {
            return $next($request); // اجازه ورود به مدیر
        }

        // چک کردن گارد دکتر، منشی یا مرکز درمانی
        if (!Auth::guard('doctor')->check() && !Auth::guard('secretary')->check() && !Auth::guard('medical_center')->check()) {
            return redirect()->to(route('dr.auth.login-register-form'));
        }

        // بررسی وضعیت دکتر
        if (Auth::guard('doctor')->check()) {
            $doctor = Auth::guard('doctor')->user();
            if ($doctor->status === 0) {
                return redirect()->to(route('dr.auth.login-register-form'));
            }
        }

        // بررسی وضعیت منشی
        if (Auth::guard('secretary')->check()) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary->status === 0) {
                return redirect()->to(route('dr.auth.login-register-form'));
            }
        }

        // بررسی وضعیت مرکز درمانی
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            if (!$medicalCenter->is_active) {
                return redirect()->to(route('dr.auth.login-register-form'));
            }
        }

        return $next($request);
    }
}
