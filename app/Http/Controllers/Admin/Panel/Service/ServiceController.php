<?php
namespace App\Http\Controllers\Admin\Panel\Service;

use App\Http\Controllers\Admin\Controller;

class ServiceController extends Controller
{
    public function index()
    {
        return view('admin.panel.services.index');
    }

    public function create()
    {
        return view('admin.panel.services.create');
    }

    public function edit($id)
    {
        return view('admin.panel.services.edit', compact('id'));
    }
}
