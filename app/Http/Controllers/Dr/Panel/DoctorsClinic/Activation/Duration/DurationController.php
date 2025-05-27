<?php

namespace App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Duration;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;
use App\Models\DoctorAppointmentConfig;

class DurationController extends Controller
{
    public function index($clinicId)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        return view('dr.panel.doctors-clinic.activation.duration.index', compact(['clinicId', 'doctorId']));
    }

    public function store(Request $request)
    {
        $request->validate([
            'clinic_id'            => 'required|exists:clinics,id',
            'doctor_id'            => 'required|exists:doctors,id',
            'appointment_duration' => 'required|integer|min:5|max:120',
            'collaboration'        => 'required|boolean',
        ]);

        // بررسی وجود رکورد
        $config = DoctorAppointmentConfig::firstOrNew([
            'clinic_id' => $request->clinic_id,
            'doctor_id' => $request->doctor_id,
        ]);

        $config->appointment_duration           = $request->appointment_duration;
        $config->collaboration_with_other_sites = $request->collaboration;
        $config->save();

        return response()->json(['success' => true, 'message' => 'اطلاعات با موفقیت ثبت شد.']);
    }
}
