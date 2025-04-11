<?php

namespace App\Http\Controllers\Admin\Panel\ClinicDepositSettings;

use App\Http\Controllers\Admin\Controller;

class ClinicDepositSettingController extends Controller
{
    public function index()
    {
        return view('admin.panel.clinic-deposit-settings.index');
    }

    public function create()
    {
        return view('admin.panel.clinic-deposit-settings.create');
    }

    public function edit($id)
    {
        return view('admin.panel.clinic-deposit-settings.edit', compact('id'));
    }
}