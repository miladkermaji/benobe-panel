<?php

namespace App\Http\Controllers\Dr\Panel\DoctorFaqs;

use App\Http\Controllers\Dr\Controller;

class DoctorFaqController extends Controller
{
    public function index()
    {
        return view('dr.panel.doctor-faqs.index');
    }

    public function create()
    {
        return view('dr.panel.doctor-faqs.create');
    }

    public function edit($id)
    {
        return view('dr.panel.doctor-faqs.edit', compact('id'));
    }
}