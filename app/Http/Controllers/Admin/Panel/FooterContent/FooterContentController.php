<?php

namespace App\Http\Controllers\Admin\Panel\FooterContent;

use App\Http\Controllers\Admin\Controller;

class FooterContentController extends Controller
{
    public function index()
    {
        return view('admin.panel.footercontents.index');
    }

    public function create()
    {
        return view('admin.panel.footercontents.create');
    }

    public function edit($id)
    {
        return view('admin.panel.footercontents.edit', compact('id'));
    }
}