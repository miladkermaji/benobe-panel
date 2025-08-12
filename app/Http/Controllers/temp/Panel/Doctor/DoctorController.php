<?php

namespace App\Http\Controllers\Mc\Panel\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    public function index()
    {
        return view('mc.panel.doctors.index');
    }

    public function create()
    {
        return view('mc.panel.doctors.create');
    }

    public function edit($id)
    {
        return view('mc.panel.doctors.edit', compact('id'));
    }
}
