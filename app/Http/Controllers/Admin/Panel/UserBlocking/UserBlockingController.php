<?php

namespace App\Http\Controllers\Admin\Panel\UserBlocking;

use App\Http\Controllers\Admin\Controller;

class UserBlockingController extends Controller
{
    public function index()
    {
        return view('admin.panel.userblockings.index');
    }

    public function create()
    {
        return view('admin.panel.userblockings.create');
    }

    public function edit($id)
    {
        return view('admin.panel.userblockings.edit', compact('id'));
    }
}