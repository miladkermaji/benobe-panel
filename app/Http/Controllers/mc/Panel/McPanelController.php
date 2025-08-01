<?php

namespace App\Http\Controllers\Mc\Panel;

use Carbon\Carbon;
use App\Models\Secretary;
use App\Models\Appointment;
use App\Models\MedicalCenter;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Mc\Controller;
use App\Traits\HasSelectedDoctor;

class McPanelController extends Controller
{
    use HasSelectedDoctor;

    public function index()
    {
        // بررسی کاربران مختلف (دکتر، منشی، مرکز درمانی)
        $user = Auth::guard('doctor')->user() ??
                Auth::guard('secretary')->user() ??
                Auth::guard('medical_center')->user();

        if (!$user) {
            return redirect()->route('mc.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }

        // اگر کاربر مرکز درمانی باشد، اطلاعات مربوط به مرکز درمانی را نمایش بده
        if (Auth::guard('medical_center')->check()) {
            /** @var MedicalCenter $medicalCenter */
            $medicalCenter = $user;

            // دریافت پزشک انتخاب‌شده
            $selectedDoctor = $medicalCenter->selectedDoctor;
            $selectedDoctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;

            // اگر پزشکی انتخاب نشده، اطلاعات کلی مرکز درمانی را نمایش بده
            if (!$selectedDoctorId) {
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
            } else {
                // اطلاعات بر اساس پزشک انتخاب‌شده
                $totalPatientsToday = Appointment::where('medical_center_id', $medicalCenter->id)
                       ->where('doctor_id', $selectedDoctorId)
                       ->whereDate('appointment_date', Carbon::today())
                       ->where('status', '!=', 'cancelled')
                       ->count();

                $visitedPatients = Appointment::where('medical_center_id', $medicalCenter->id)
                    ->where('doctor_id', $selectedDoctorId)
                    ->where('status', 'attended')
                    ->whereDate('appointment_date', Carbon::today())
                    ->count();

                $remainingPatients = Appointment::where('medical_center_id', $medicalCenter->id)
                    ->where('doctor_id', $selectedDoctorId)
                    ->where('status', 'scheduled')
                    ->whereDate('appointment_date', Carbon::today())
                    ->where('attendance_status', null)
                    ->count();

                $weeklyIncome = Appointment::where('medical_center_id', $medicalCenter->id)
                    ->where('doctor_id', $selectedDoctorId)
                    ->where('payment_status', 'paid')
                    ->where('status', 'attended')
                    ->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])
                    ->sum('final_price');

                $monthlyIncome = Appointment::where('medical_center_id', $medicalCenter->id)
                    ->where('doctor_id', $selectedDoctorId)
                    ->where('payment_status', 'paid')
                    ->where('status', 'attended')
                    ->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->sum('final_price');

                $totalIncome = Appointment::where('medical_center_id', $medicalCenter->id)
                    ->where('doctor_id', $selectedDoctorId)
                    ->where('payment_status', 'paid')
                    ->where('status', 'attended')
                    ->sum('final_price');
            }

            $selectedMedicalCenterId = $medicalCenter->id;

            return view('mc.panel.index', compact(
                'totalPatientsToday',
                'visitedPatients',
                'remainingPatients',
                'weeklyIncome',
                'monthlyIncome',
                'totalIncome',
                'selectedMedicalCenterId',
                'selectedDoctorId',
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
        $selectedDoctorId = null; // برای دکتر و منشی، پزشک انتخاب‌شده نداریم

        return view('mc.panel.index', compact(
            'totalPatientsToday',
            'visitedPatients',
            'remainingPatients',
            'weeklyIncome',
            'monthlyIncome',
            'totalIncome',
            'selectedMedicalCenterId',
            'selectedDoctorId',
        ));
    }
}
