<?php

namespace App\Http\Controllers\Admin\Panel\DoctorSpecialty;

use App\Http\Controllers\Admin\Controller;

class DoctorSpecialtyController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctorspecialties.index');
    }

    public function create()
    {
        return view('admin.panel.doctorspecialties.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctorspecialties.edit', compact('id'));
    }
}