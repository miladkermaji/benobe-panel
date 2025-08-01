<?php

namespace App\Http\Controllers\Dr\Panel;

use Illuminate\Http\Request;
use App\Http\Controllers\Dr\Controller;

class DoctorPrescriptionController extends Controller
{
    public function index()
    {
        return view('dr.panel.doctor-prescriptions.index');
    }

    public function settings()
    {
        return view('dr.panel.doctor-prescriptions.settings');
    }
}
