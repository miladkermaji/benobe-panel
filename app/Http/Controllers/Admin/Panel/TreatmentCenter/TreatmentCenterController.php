<?php

namespace App\Http\Controllers\Admin\Panel\TreatmentCenter;

use App\Http\Controllers\Admin\Controller;

class TreatmentCenterController extends Controller
{
    public function index()
    {
        return view('admin.panel.treatment-centers.index');
    }

    public function create()
    {
        return view('admin.panel.treatment-centers.create');
    }

    public function edit($id)
    {
        return view('admin.panel.treatment-centers.edit', compact('id'));
    }
    public function gallery($id)
    {
        return view('admin.panel.treatment-centers.gallery', compact('id'));
    }
}
