<?php

namespace App\Http\Controllers\Admin\Panel;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorClinicController extends Controller
{
    public function index($doctorId)
    {
        $doctor = Doctor::findOrFail($doctorId);
        return view('admin.panel.doctors.clinics', compact('doctor'));
    }
}
