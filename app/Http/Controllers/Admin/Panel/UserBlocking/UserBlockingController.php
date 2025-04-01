<?php

namespace App\Http\Controllers\Admin\Panel\UserBlocking;

use App\Http\Controllers\Admin\Controller;

class UserBlockingController extends Controller
{
    public function index()
    {
        return view('admin.panel.user-blockings.index');
    }

    public function create()
    {
        return view('admin.panel.user-blockings.create');
    }

    public function edit($id)
    {
        return view('admin.panel.user-blockings.edit', compact('id'));
    }
}
