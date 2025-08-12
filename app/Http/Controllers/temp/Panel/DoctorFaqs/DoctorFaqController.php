<?php

namespace App\Http\Controllers\Mc\Panel\DoctorFaqs;

use App\Http\Controllers\Mc\Controller;

class DoctorFaqController extends Controller
{
    public function index()
    {
        return view('mc.panel.doctor-faqs.index');
    }

    public function create()
    {
        return view('mc.panel.doctor-faqs.create');
    }

    public function edit($id)
    {
        return view('mc.panel.doctor-faqs.edit', compact('id'));
    }
}
