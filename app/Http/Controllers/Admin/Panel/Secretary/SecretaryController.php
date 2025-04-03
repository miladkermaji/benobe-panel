<?php

namespace App\Http\Controllers\Admin\Panel\Secretary;

use App\Http\Controllers\Admin\Controller;

class SecretaryController extends Controller
{
    public function index()
    {
        return view('admin.panel.secretaries.index');
    }

    public function create()
    {
        return view('admin.panel.secretaries.create');
    }

    public function edit($id)
    {
        return view('admin.panel.secretaries.edit', compact('id'));
    }
}