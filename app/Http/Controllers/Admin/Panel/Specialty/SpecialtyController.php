<?php
namespace App\Http\Controllers\Admin\Panel\Specialty;

use App\Http\Controllers\Admin\Controller;

class SpecialtyController extends Controller
{
    public function index()
    {
        return view('admin.panel.specialties.index');
    }

    public function create()
    {
        return view('admin.panel.specialties.create');
    }

    public function edit($id)
    {
        return view('admin.panel.specialties.edit', compact('id'));
    }
}
