<?php

namespace App\Http\Controllers\Admin\Panel\DoctorService;

use App\Http\Controllers\Admin\Controller;

class DoctorServiceController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctorservices.index');
    }

    public function create()
    {
        return view('admin.panel.doctorservices.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctorservices.edit', compact('id'));
    }
}
