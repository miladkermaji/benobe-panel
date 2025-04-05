<?php

namespace App\Http\Controllers\Admin\Panel\Insurance;

use App\Http\Controllers\Admin\Controller;

class InsuranceController extends Controller
{
    public function index()
    {
        return view('admin.panel.insurances.index');
    }

    public function create()
    {
        return view('admin.panel.insurances.create');
    }

    public function edit($id)
    {
        return view('admin.panel.insurances.edit', compact('id'));
    }
}