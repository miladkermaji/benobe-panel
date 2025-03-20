<?php
namespace App\Http\Controllers\Admin\Panel\Clinic;

use App\Http\Controllers\Admin\Controller;

class ClinicController extends Controller
{
    public function index()
    {
        return view('admin.panel.clinics.index');
    }

    public function create()
    {
        return view('admin.panel.clinics.create');
    }

    public function edit($id)
    {
        return view('admin.panel.clinics.edit', compact('id'));
    }

    public function gallery($id)
    {
        return view('admin.panel.clinics.gallery', compact('id'));
    }
}
