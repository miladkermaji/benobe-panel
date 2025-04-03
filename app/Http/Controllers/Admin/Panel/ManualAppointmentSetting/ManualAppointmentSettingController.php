<?php

namespace App\Http\Controllers\Admin\Panel\ManualAppointmentSetting;

use App\Http\Controllers\Admin\Controller;

class ManualAppointmentSettingController extends Controller
{
    public function index()
    {
        return view('admin.panel.manual-appointment-settings.index');
    }

    public function create()
    {
        return view('admin.panel.manual-appointment-settings.create');
    }

    public function edit($id)
    {
        return view('admin.panel.manual-appointment-settings.edit', compact('id'));
    }
}