<?php

namespace App\Http\Controllers\Admin\Panel\DoctorDocument;

use App\Http\Controllers\Admin\Controller;

class DoctorDocumentController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctordocuments.index');
    }

    public function create()
    {
        return view('admin.panel.doctordocuments.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctordocuments.edit', compact('id'));
    }
}