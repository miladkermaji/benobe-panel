<?php

namespace App\Http\Controllers\Admin\ContentManagement\Blog;

use App\Http\Controllers\Admin\Controller;

class BlogController extends Controller
{
    public function index()
    {
        return view('admin.content.content-management.blog.index');
    }

    public function create()
    {
        return view('admin.content.content-management.blog.create');
    }

    public function edit($id)
    {
        return view('admin.content.content-management.blog.edit', compact('id'));
    }
}