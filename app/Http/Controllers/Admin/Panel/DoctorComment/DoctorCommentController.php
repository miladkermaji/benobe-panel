<?php

namespace App\Http\Controllers\Admin\Panel\DoctorComment;

use App\Http\Controllers\Admin\Controller;

class DoctorCommentController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctor-comments.index');
    }

    public function create()
    {
        return view('admin.panel.doctor-comments.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctor-comments.edit', compact('id'));
    }
}
