<?php

namespace App\Http\Controllers\Admin\Panel\DoctorInsurance;

use App\Http\Controllers\Admin\Controller;

class DoctorInsuranceController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctor-insurances.index');
    }

    public function create()
    {
        return view('admin.panel.doctor-insurances.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctor-insurances.edit', compact('id'));
    }
}