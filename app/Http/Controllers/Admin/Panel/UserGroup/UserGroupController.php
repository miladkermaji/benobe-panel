<?php

namespace App\Http\Controllers\Admin\Panel\UserGroup;

use App\Http\Controllers\Admin\Controller;

class UserGroupController extends Controller
{
    public function index()
    {
        return view('admin.panel.usergroups.index');
    }

    public function create()
    {
        return view('admin.panel.usergroups.create');
    }

    public function edit($id)
    {
        return view('admin.panel.usergroups.edit', compact('id'));
    }
}
