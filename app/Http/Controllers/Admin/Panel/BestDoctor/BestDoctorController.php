<?php

namespace App\Http\Controllers\Admin\Panel\BestDoctor;

use App\Http\Controllers\Admin\Controller;

class BestDoctorController extends Controller
{
    public function index()
    {
        return view('admin.panel.bestdoctors.index');
    }

    public function create()
    {
        return view('admin.panel.bestdoctors.create');
    }

    public function edit($bestdoctorId)
    {
        return view('admin.panel.bestdoctors.edit', compact('bestdoctorId'));
    }
}