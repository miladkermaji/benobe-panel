<?php

namespace App\Http\Controllers\Admin\Panel\SubUser;

use App\Http\Controllers\Admin\Controller;

class SubUserController extends Controller
{
    public function index()
    {
        return view('admin.panel.subusers.index');
    }

    public function create()
    {
        return view('admin.panel.subusers.create');
    }

    public function edit($id)
    {
        return view('admin.panel.subusers.edit', compact('id'));
    }
}