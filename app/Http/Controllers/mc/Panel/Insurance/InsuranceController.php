<?php

namespace App\Http\Controllers\Mc\Panel\Insurance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class InsuranceController extends Controller
{
    public function index()
    {
        return view('mc.panel.insurances.index');
    }

    public function create()
    {
        return view('mc.panel.insurances.create');
    }

    public function edit($id)
    {
        return view('mc.panel.insurances.edit', compact('id'));
    }
}
