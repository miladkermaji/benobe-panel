<?php

namespace App\Http\Controllers\Dr\Panel\Secretary;

use App\Http\Controllers\Dr\Controller;
use App\Models\Secretary;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SecretaryManagementController extends Controller
{
    public function index(Request $request)
    {
        return view('dr.panel.secretary.index');
    }

    public function create()
    {
        return view('dr.panel.secretary.create');
    }

    public function edit($id)
    {
        return view('dr.panel.secretary.edit');
    }
}
