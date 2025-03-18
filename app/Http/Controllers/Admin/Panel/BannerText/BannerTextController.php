<?php

namespace App\Http\Controllers\Admin\Panel\BannerText;

use App\Http\Controllers\Admin\Controller;

class BannerTextController extends Controller
{
    public function index()
    {
        return view('admin.panel.bannertexts.index');
    }

    public function create()
    {
        return view('admin.panel.bannertexts.create');
    }

    public function edit($id)
    {
        return view('admin.panel.bannertexts.edit', compact('id'));
    }
}