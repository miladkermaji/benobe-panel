<?php

namespace App\Http\Controllers\Mc\Panel\Secretary;

use App\Http\Controllers\Mc\Controller;
use App\Models\Secretary;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecretaryManagementController extends Controller
{
    public function index(Request $request)
    {
        return view('mc.panel.secretary.index');
    }

    public function create()
    {
        return view('mc.panel.secretary.create');
    }

    public function edit($id)
    {
        return view('mc.panel.secretary.edit');
    }
}
