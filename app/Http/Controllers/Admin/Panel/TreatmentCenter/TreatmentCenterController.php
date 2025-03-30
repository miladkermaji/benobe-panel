<?php

namespace App\Http\Controllers\Admin\Panel\TreatmentCenter;

use App\Http\Controllers\Admin\Controller;

class TreatmentCenterController extends Controller
{
    public function index()
    {
        return view('admin.panel.treatmentcenters.index');
    }

    public function create()
    {
        return view('admin.panel.treatmentcenters.create');
    }

    public function edit($id)
    {
        return view('admin.panel.treatmentcenters.edit', compact('id'));
    }
    public function gallery($id)
    {
        return view('admin.panel.treatmentcenters.gallery', compact('id'));
    }
}
