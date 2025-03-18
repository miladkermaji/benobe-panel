<?php
namespace App\Http\Controllers\Admin\Panel\Menu;

use App\Http\Controllers\Admin\Controller;

class MenuController extends Controller
{
    public function index()
    {
        return view('admin.panel.menus.index');
    }

    public function create()
    {
        return view('admin.panel.menus.create');
    }

    public function edit($id)
    {
        return view('admin.panel.menus.edit', compact('id'));
    }
}
