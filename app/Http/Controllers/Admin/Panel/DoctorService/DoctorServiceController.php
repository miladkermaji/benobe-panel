<?php

namespace App\Http\Controllers\Admin\Panel\DoctorService;

use App\Http\Controllers\Admin\Controller;

class DoctorServiceController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctor-services.index');
    }

    public function create()
    {
        return view('admin.panel.doctor-services.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctor-services.edit', compact('id'));
    }
}
