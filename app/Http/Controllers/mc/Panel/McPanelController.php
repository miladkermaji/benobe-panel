<?php

namespace App\Http\Controllers\Mc\Panel;

use Carbon\Carbon;
use App\Models\Secretary;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;

class McPanelController extends Controller
{
    public function index()
    {
        // بررسی کاربران مختلف (دکتر، منشی، مرکز درمانی)
        $user = Auth::guard('doctor')->user() ??
                Auth::guard('secretary')->user() ??
                Auth::guard('medical_center')->user();

        if (!$user) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }

        // اگر کاربر مرکز درمانی باشد، اطلاعات مربوط به مرکز درمانی را نمایش بده
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = $user;

            // اطلاعات آماری برای مرکز درمانی
            $totalPatientsToday = Appointment::where('medical_center_id', $medicalCenter->id)
                   ->whereDate('appointment_date', Carbon::today())
                   ->where('status', '!=', 'cancelled')
                   ->count();

            $visitedPatients = Appointment::where('medical_center_id', $medicalCenter->id)
                ->where('status', 'attended')
                ->whereDate('appointment_date', Carbon::today())
                ->count();

            $remainingPatients = Appointment::where('medical_center_id', $medicalCenter->id)
                ->where('status', 'scheduled')
                ->whereDate('appointment_date', Carbon::today())
                ->where('attendance_status', null)
                ->count();

            $weeklyIncome = Appointment::where('medical_center_id', $medicalCenter->id)
                ->where('payment_status', 'paid')
                ->where('status', 'attended')
                ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                ->sum('final_price');

            $monthlyIncome = Appointment::where('medical_center_id', $medicalCenter->id)
                ->where('payment_status', 'paid')
                ->where('status', 'attended')
                ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                ->sum('final_price');

            $totalIncome = Appointment::where('medical_center_id', $medicalCenter->id)
                ->where('payment_status', 'paid')
                ->where('status', 'attended')
                ->sum('final_price');

            $selectedMedicalCenterId = $medicalCenter->id;

            return view('dr.panel.index', compact(
                'totalPatientsToday',
                'visitedPatients',
                'remainingPatients',
                'weeklyIncome',
                'monthlyIncome',
                'totalIncome',
                'selectedMedicalCenterId',
            ));
        }

        // برای دکتر و منشی (کد قبلی)
        $doctorId = $user instanceof \App\Models\Doctor ? $user->id : $user->doctor_id;

        // تعداد بیماران امروز
        $totalPatientsToday = Appointment::where('doctor_id', $doctorId)
               ->whereDate('appointment_date', Carbon::today())
               ->where('status', '!=', 'cancelled')
               ->count();

        // بیماران ویزیت‌شده
        $visitedPatients = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'attended')
            ->whereDate('appointment_date', Carbon::today())
            ->count();

        // بیماران باقی‌مانده
        $remainingPatients = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'scheduled')
            ->whereDate('appointment_date', Carbon::today())
            ->where('attendance_status', null)
            ->count();

        // درآمد این هفته
        $weeklyIncome = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->where('status', 'attended')
            ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
            ->sum('final_price');

        // درآمد این ماه
        $monthlyIncome = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->where('status', 'attended')
            ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
            ->sum('final_price');

        // درآمد کلی
        $totalIncome = Appointment::where('doctor_id', $doctorId)
            ->where('payment_status', 'paid')
            ->where('status', 'attended')
            ->sum('final_price');
        $selectedMedicalCenterId = $this->getSelectedMedicalCenterId();
        return view('dr.panel.index', compact(
            'totalPatientsToday',
            'visitedPatients',
            'remainingPatients',
            'weeklyIncome',
            'monthlyIncome',
            'totalIncome',
            'selectedMedicalCenterId',
        ));
    }


}
