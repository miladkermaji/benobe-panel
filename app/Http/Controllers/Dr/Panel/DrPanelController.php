<?php

namespace App\Http\Controllers\Dr\Panel;

use Carbon\Carbon;
use App\Models\Clinic;
use App\Models\Secretary;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;

class DrPanelController extends Controller
{
    public function index()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

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
        $selectedClinicId = $this->getSelectedClinicId();
        return view('dr.panel.index', compact(
            'totalPatientsToday',
            'visitedPatients',
            'remainingPatients',
            'weeklyIncome',
            'monthlyIncome',
            'totalIncome',
            'selectedClinicId',
        ));
    }


}
