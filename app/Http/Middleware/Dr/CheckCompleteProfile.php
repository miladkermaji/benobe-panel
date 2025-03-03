<?php

namespace App\Http\Middleware\Dr;

use Closure;
use Illuminate\Support\Facades\Auth;

class CheckCompleteProfile
{
    public function handle($request, Closure $next)
    {
        // مستثنی کردن درخواست‌های JSON، Livewire، و مسیرهای لاگین
        if (
            $request->expectsJson() ||
            $request->is('livewire/*') ||
            $request->is('admin/*') ||
            $request->is('admin-panel/*') ||
            $request->routeIs('dr.auth.login-register-form') ||
            $request->routeIs('dr.auth.login-user-pass-form') ||
            $request->routeIs('admin-panel.login') // مسیر لاگین ادمین
        ) {
            return $next($request);
        }

        $doctor = Auth::guard('doctor')->user();

        // چک کردن احراز هویت و تکمیل پروفایل
        if (Auth::guard('doctor')->check() && !$doctor->profile_completed) {
            // اجازه دسترسی به مسیرهای ویرایش پروفایل
            if (
                $request->routeIs('dr-edit-profile') ||
                $request->routeIs('dr-update-profile') ||
                $request->routeIs('dr-send-mobile-otp') ||
                $request->routeIs('dr-mobile-confirm') ||
                $request->routeIs('dr-specialty-update') ||
                $request->routeIs('dr-uuid-update')
            ) {
                return $next($request);
            }

            // ریدایرکت به صفحه ویرایش پروفایل
            return redirect()->route('dr-edit-profile')
                ->with('complete-profile', 'برای دسترسی به امکانات سایت لطفا ابتدا پروفایل خود را تکمیل کنید');
        }

        return $next($request);
    }
}