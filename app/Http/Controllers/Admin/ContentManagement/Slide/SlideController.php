<?php

namespace App\Http\Controllers\Admin\ContentManagement\Slide;

use App\Http\Controllers\Admin\Controller;

class SlideController extends Controller
{
    public function index()
    {
        return view('admin.content.content-management.slide.index');
    }

    public function create()
    {
        return view('admin.content.content-management.slide.create');
    }

    public function edit($id)
    {
        return view('admin.content.content-management.slide.edit', compact('id'));
    }
}