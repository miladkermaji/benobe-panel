<?php

namespace App\Http\Controllers\Admin\Panel\DoctorDocument;

use App\Http\Controllers\Admin\Controller;

class DoctorDocumentController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctor-documents.index');
    }

    public function create()
    {
        return view('admin.panel.doctor-documents.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctor-documents.edit', compact('id'));
    }
}
