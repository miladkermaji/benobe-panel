<?php

namespace App\Http\Controllers\Admin\Panel\Doctor;

use App\Http\Controllers\Admin\Controller;

class DoctorController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctors.index');
    }

    public function create()
    {
        return view('admin.panel.doctors.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctors.edit', compact('id'));
    }
}
