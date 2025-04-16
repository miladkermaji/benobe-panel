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
        $today = Carbon::today();
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $totalPatientsToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->count();
        $visitedPatients = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('attendance_status', 'attended')
            ->count();
        $remainingPatients = $totalPatientsToday - $visitedPatients;

        $secretriesCount = Secretary::where('doctor_id', $doctorId)
                  ->count();

        $clinicsCount = Clinic::where('doctor_id', $doctorId)
                          ->count();


        return view("dr.panel.index", compact('totalPatientsToday', 'visitedPatients', 'remainingPatients', 'secretriesCount', 'clinicsCount'));
    }

}
