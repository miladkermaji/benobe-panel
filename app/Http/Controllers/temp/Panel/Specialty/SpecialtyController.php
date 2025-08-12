<?php

namespace App\Http\Controllers\Mc\Panel\Specialty;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SpecialtyController extends Controller
{
    public function index()
    {
        return view('mc.panel.specialties.index');
    }

    public function create()
    {
        return view('mc.panel.specialties.create');
    }

    public function edit($id)
    {
        return view('mc.panel.specialties.edit', compact('id'));
    }
}
