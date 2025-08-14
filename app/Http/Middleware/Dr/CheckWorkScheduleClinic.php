<?php

namespace App\Http\Middleware\Dr;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\DoctorWorkSchedule;
use App\Models\MedicalCenter;

class CheckWorkScheduleClinic
{
    public function handle(Request $request, Closure $next)
    {
        // مستثنی کردن درخواست‌های JSON، Livewire، و مسیرهای لاگین
        if (
            $request->expectsJson() ||
            $request->is('livewire/*') ||
            $request->is('admin/*') ||
            $request->is('admin-panel/*') ||
            $request->routeIs('dr.auth.login-register-form') ||
            $request->routeIs('dr.auth.login-user-pass-form') ||
            $request->routeIs('admin-panel.login') ||
            // صفحات مدیریت/ایجاد/ویرایش مطب
            $request->routeIs('dr-clinic-management') ||
            $request->routeIs('dr-clinic-store') ||
            $request->routeIs('dr-clinic-edit') ||
            $request->routeIs('dr-clinic-update') ||
            $request->routeIs('dr-clinic-destroy') ||
            $request->routeIs('dr.panel.clinics.create') ||
            $request->routeIs('dr.panel.clinics.edit') ||
            // سایر صفحات مربوطه
            $request->routeIs('dr-office-gallery') ||
            $request->routeIs('dr-office-medicalDoc') ||
            $request->routeIs('doctors.clinic.deposit') ||
            $request->routeIs('doctors.clinic.deposit.store') ||
            $request->routeIs('doctors.clinic.deposit.update') ||
            $request->routeIs('doctors.clinic.update.address') ||
            $request->routeIs('doctors.clinic.get.phones') ||
            $request->routeIs('doctors.clinic.update.phones') ||
            $request->routeIs('doctors.clinic.delete.phone') ||
            $request->routeIs('doctors.clinic.get.secretary.phone') ||
            $request->routeIs('doctors.clinic.cost') ||
            $request->routeIs('cost.list') ||
            $request->routeIs('cost.delete') ||
            $request->routeIs('duration.store') ||
            $request->routeIs('duration.index') ||
            $request->routeIs('activation.workhours.index') ||
            $request->routeIs('workhours.get') ||
            $request->routeIs('activation.workhours.store') ||
            $request->routeIs('activation.workhours.delete') ||
            $request->routeIs('start.appointment') ||
            $request->routeIs('cost.store')
        ) {
            return $next($request);
        }

        // فقط برای پزشک و منشی
        if (!Auth::guard('doctor')->check() && !Auth::guard('secretary')->check()) {
            return $next($request);
        }

        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

        // بررسی وجود مطب policlinic برای این پزشک
        $hasPoliclinic = \App\Models\MedicalCenter::whereHas('doctors', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })
        ->where('type', 'policlinic')
        ->exists();

        // بررسی وجود ساعت کاری بدون مطب
        $hasWorkScheduleWithoutClinic = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('is_working', true)
            ->whereNull('medical_center_id')
            ->exists();

        // بررسی وجود ساعت کاری تخصیص شده به مطب policlinic
        $hasWorkScheduleWithClinic = DoctorWorkSchedule::where('doctor_id', $doctorId)
            ->where('is_working', true)
            ->whereNotNull('medical_center_id')
            ->whereHas('medicalCenter', function ($query) {
                $query->where('type', 'policlinic');
            })
            ->exists();

        // اگر پزشک مطب policlinic دارد ولی ساعات کاری به آن تخصیص نشده
        if ($hasPoliclinic && !$hasWorkScheduleWithClinic) {
            // ذخیره اطلاعات در session برای نمایش مودال
            session()->put('show_clinic_modal', true);
            session()->put('doctor_work_schedule_data', [
                'doctor_id' => $doctorId,
                'has_work_schedule' => $hasWorkScheduleWithoutClinic,
                'has_clinic' => true,
                'clinic_type' => 'policlinic',
                'needs_work_hours_assignment' => true
            ]);
        }
        // اگر پزشک مطب ندارد ولی ساعات کاری بدون مطب دارد
        elseif (!$hasPoliclinic && $hasWorkScheduleWithoutClinic) {
            session()->put('show_clinic_modal', true);
            session()->put('doctor_work_schedule_data', [
                'doctor_id' => $doctorId,
                'has_work_schedule' => true,
                'has_clinic' => false,
                'needs_clinic_creation' => true
            ]);
        }

        return $next($request);
    }
}
