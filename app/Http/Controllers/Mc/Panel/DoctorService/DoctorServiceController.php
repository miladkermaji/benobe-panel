<?php

namespace App\Http\Controllers\Mc\Panel\DoctorService;

use App\Http\Controllers\Mc\Controller;

class DoctorServiceController extends Controller
{
    public function index()
    {
        return view('mc.panel.doctor-services.index');
    }

    public function create()
    {
        return view('mc.panel.doctor-services.create');
    }

    public function edit($id)
    {
        return view('mc.panel.doctor-services.edit', compact('id'));
    }
}
