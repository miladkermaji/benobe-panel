<?php

namespace App\Http\Controllers\Admin\ContentManagement\Tags;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;

class TagsController extends Controller
{
    public function index()
    {
        return view('admin.content.content-management.tags.index');
    }

    public function create()
    {
        return view('admin.content.content-management.tags.create');
    }

    public function edit(string $id)
    {
        return view('admin.content.content-management.tags.edit', compact('id'));
    }
}