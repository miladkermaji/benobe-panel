<?php

namespace App\Http\Controllers\Dr\Panel\DoctorService;

use App\Http\Controllers\Dr\Controller;

class DoctorServiceController extends Controller
{
    public function index()
    {
        return view('dr.panel.doctor-services.index');
    }

    public function create()
    {
        return view('dr.panel.doctor-services.create');
    }

    public function edit($id)
    {
        return view('dr.panel.doctor-services.edit', compact('id'));
    }
}
