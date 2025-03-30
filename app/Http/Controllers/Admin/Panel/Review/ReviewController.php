<?php

namespace App\Http\Controllers\Admin\Panel\Review;

use App\Http\Controllers\Admin\Controller;

class ReviewController extends Controller
{
    public function index()
    {
        return view('admin.panel.reviews.index');
    }

    public function create()
    {
        return view('admin.panel.reviews.create');
    }

    public function edit($id)
    {
        return view('admin.panel.reviews.edit', compact('id'));
    }
}
