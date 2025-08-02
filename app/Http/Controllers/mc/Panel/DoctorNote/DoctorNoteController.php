<?php

namespace App\Http\Controllers\Mc\Panel\DoctorNote;

use App\Http\Controllers\Mc\Controller;

class DoctorNoteController extends Controller
{
    public function index()
    {
        return view('mc.panel.doctor-notes.index');
    }

    public function create()
    {
        return view('mc.panel.doctor-notes.create');
    }

    public function edit($id)
    {
        return view('mc.panel.doctor-notes.edit', compact('id'));
    }
}
