<?php
namespace App\Http\Controllers\Admin\Panel\ImagingCenter;

use App\Http\Controllers\Admin\Controller;

class ImagingCenterController extends Controller
{
    public function index()
    {
        return view('admin.panel.imagingcenters.index');
    }

    public function create()
    {
        return view('admin.panel.imagingcenters.create');
    }

    public function edit($id)
    {
        return view('admin.panel.imagingcenters.edit', compact('id'));
    }

    public function gallery($id)
    {
        return view('admin.panel.imagingcenters.gallery', compact('id'));
    }
}