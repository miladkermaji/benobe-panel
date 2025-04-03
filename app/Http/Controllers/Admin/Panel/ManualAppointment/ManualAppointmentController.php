<?php

namespace App\Http\Controllers\Admin\Panel\ManualAppointment;

use App\Http\Controllers\Admin\Controller;

class ManualAppointmentController extends Controller
{
    public function index()
    {
        return view('admin.panel.manual-appointments.index');
    }

    public function create()
    {
        return view('admin.panel.manual-appointments.create');
    }

    public function edit($id)
    {
        return view('admin.panel.manual-appointments.edit', compact('id'));
    }
}