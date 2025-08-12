<?php

namespace App\Http\Controllers\Mc\Panel\Service;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        return view('mc.panel.services.index');
    }

    public function create()
    {
        return view('mc.panel.services.create');
    }

    public function edit($id)
    {
        return view('mc.panel.services.edit', compact('id'));
    }
}
