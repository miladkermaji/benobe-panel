<?php

namespace App\Http\Controllers\Admin\Panel\Stories;

use App\Http\Controllers\Admin\Controller;

class StoriesController extends Controller
{
    public function index()
    {
        return view('admin.panel.stories.index');
    }

    public function create()
    {
        return view('admin.panel.stories.create');
    }

    public function edit($id)
    {
        return view('admin.panel.stories.edit', compact('id'));
    }

    public function analytics()
    {
        return view('admin.panel.stories.analytics');
    }
}
