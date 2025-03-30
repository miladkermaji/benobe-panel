<?php

namespace App\Http\Controllers\Admin\Panel\Hospital;

use App\Http\Controllers\Admin\Controller;

class HospitalController extends Controller
{
    public function index()
    {
        return view('admin.panel.hospitals.index');
    }

    public function create()
    {
        return view('admin.panel.hospitals.create');
    }

    public function edit($id)
    {
        return view('admin.panel.hospitals.edit', compact('id'));
    }
    public function gallery($id)
    {
        return view('admin.panel.hospitals.gallery', compact('id'));
    }
}
