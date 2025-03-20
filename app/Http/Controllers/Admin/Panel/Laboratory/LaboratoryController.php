<?php

namespace App\Http\Controllers\Admin\Panel\Laboratory;

use App\Http\Controllers\Admin\Controller;

class LaboratoryController extends Controller
{
    public function index()
    {
        return view('admin.panel.laboratories.index');
    }

    public function create()
    {
        return view('admin.panel.laboratories.create');
    }

    public function edit($id)
    {
        return view('admin.panel.laboratories.edit', compact('id'));
    }
     public function gallery($id)
    {
        return view('admin.panel.laboratories.gallery', compact('id'));
    }
}