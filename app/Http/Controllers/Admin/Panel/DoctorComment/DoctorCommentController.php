<?php

namespace App\Http\Controllers\Admin\Panel\DoctorComment;

use App\Http\Controllers\Admin\Controller;

class DoctorCommentController extends Controller
{
    public function index()
    {
        return view('admin.panel.doctorcomments.index');
    }

    public function create()
    {
        return view('admin.panel.doctorcomments.create');
    }

    public function edit($id)
    {
        return view('admin.panel.doctorcomments.edit', compact('id'));
    }
}