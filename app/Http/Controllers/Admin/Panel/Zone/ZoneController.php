<?php
namespace App\Http\Controllers\Admin\Panel\Zone;

use App\Http\Controllers\Admin\Controller;

class ZoneController extends Controller
{
    public function index()
    {
        return view('admin.panel.zones.index');
    }

    public function create()
    {
        return view('admin.panel.zones.create');
    }

    public function edit($id)
    {
        return view('admin.panel.zones.edit', compact('id'));
    }
    public function citiesIndex()
    {
        return view('admin.panel.cities.index');
    }

    public function citiesCreate()
    {
        return view('admin.panel.cities.create');
    }

    public function citiesEdit($id)
    {
        return view('admin.panel.cities.edit', compact('id'));
    }
}
