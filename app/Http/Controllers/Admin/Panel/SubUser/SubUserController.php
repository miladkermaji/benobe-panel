<?php

namespace App\Http\Controllers\Admin\Panel\SubUser;

use App\Http\Controllers\Admin\Controller;

class SubUserController extends Controller
{
    public function index()
    {
        return view('admin.panel.sub-users.index');
    }

    public function create()
    {
        return view('admin.panel.sub-users.create');
    }

    public function edit($id)
    {
        return view('admin.panel.sub-users.edit', compact('id'));
    }
}
