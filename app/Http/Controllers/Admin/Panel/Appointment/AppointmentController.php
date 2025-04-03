<?php

namespace App\Http\Controllers\Admin\Panel\Appointment;

use App\Http\Controllers\Admin\Controller;

class AppointmentController extends Controller
{
    public function index()
    {
        return view('admin.panel.appointments.index');
    }

    public function create()
    {
        return view('admin.panel.appointments.create');
    }

    public function edit($id)
    {
        return view('admin.panel.appointments.edit', compact('id'));
    }
}