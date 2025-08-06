<?php

namespace App\Http\Controllers\Admin\Panel\Managers;

use App\Http\Controllers\Admin\Controller;

class ManagerController extends Controller
{
    public function index()
    {
        return view('admin.panel.managers.index');
    }

    public function create()
    {
        return view('admin.panel.managers.create');
    }

    public function edit($id)
    {
        return view('admin.panel.managers.edit', compact('id'));
    }
}
