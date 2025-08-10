<?php

namespace App\Http\Controllers\Admin\Panel\Faq;

use App\Http\Controllers\Admin\Controller;

class FaqController extends Controller
{
    public function index()
    {
        return view('admin.panel.faqs.index');
    }

    public function create()
    {
        return view('admin.panel.faqs.create');
    }

    public function edit($id)
    {
        return view('admin.panel.faqs.edit', compact('id'));
    }
}
