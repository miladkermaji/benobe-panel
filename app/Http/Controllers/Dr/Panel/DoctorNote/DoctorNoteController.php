<?php

namespace App\Http\Controllers\Dr\Panel\DoctorNote;

use App\Http\Controllers\Dr\Controller;

class DoctorNoteController extends Controller
{
    public function index()
    {
        return view('dr.panel.doctornotes.index');
    }

    public function create()
    {
        return view('dr.panel.doctornotes.create');
    }

    public function edit($id)
    {
        return view('dr.panel.doctornotes.edit', compact('id'));
    }
}