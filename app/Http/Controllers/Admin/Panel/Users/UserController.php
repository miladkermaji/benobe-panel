<?php

namespace App\Http\Controllers\Admin\Panel\Users;

use App\Http\Controllers\Admin\Controller;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.panel.users.index');
    }

    public function create()
    {
        return view('admin.panel.users.create');
    }

    public function edit($id)
    {
        return view('admin.panel.users.edit', compact('id'));
    }
}
