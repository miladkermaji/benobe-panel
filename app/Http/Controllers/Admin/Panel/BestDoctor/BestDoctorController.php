<?php

namespace App\Http\Controllers\Admin\Panel\BestDoctor;

use App\Http\Controllers\Admin\Controller;

class BestDoctorController extends Controller
{
    public function index()
    {
        return view('admin.panel.best-doctors.index');
    }

    public function create()
    {
        return view('admin.panel.best-doctors.create');
    }

    public function edit($bestdoctorId)
    {
        return view('admin.panel.best-doctors.edit', compact('bestdoctorId'));
    }
}
