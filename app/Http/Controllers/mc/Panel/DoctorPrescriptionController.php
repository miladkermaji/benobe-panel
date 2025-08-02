<?php

namespace App\Http\Controllers\Mc\Panel;

use Illuminate\Http\Request;
use App\Http\Controllers\Mc\Controller;

class DoctorPrescriptionController extends Controller
{
    public function index()
    {
        return view('mc.panel.doctor-prescriptions.index');
    }

    public function settings()
    {
        return view('mc.panel.doctor-prescriptions.settings');
    }
}
