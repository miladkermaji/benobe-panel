<?php

namespace App\Http\Controllers\Admin\Panel\DoctorHoliday;

use App\Http\Controllers\Admin\Controller;

class DoctorHolidayController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctor-holidays.index');
    }

    public function create()
    {
        return view('admin.panel.doctor-holidays.create');
    }

    public function edit($id, $date = null)
    {
        return view('admin.panel.doctor-holidays.edit', [
            'id' => $id,
            'date' => $date,
        ]);
    }
}
