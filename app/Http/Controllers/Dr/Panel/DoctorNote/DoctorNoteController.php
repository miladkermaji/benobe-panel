<?php

namespace App\Http\Controllers\Dr\Panel\DoctorNote;

use App\Http\Controllers\Dr\Controller;

class DoctorNoteController extends Controller
{
    public function index()
    {
        return view('dr.panel.doctor-notes.index');
    }

    public function create()
    {
        return view('dr.panel.doctor-notes.create');
    }

    public function edit($id)
    {
        return view('dr.panel.doctor-notes.edit', compact('id'));
    }
}
