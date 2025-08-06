<?php

namespace App\Http\Controllers\Mc\Panel\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class MedicalCenterProfileController extends Controller
{
    public function edit()
    {
        return view('mc.panel.profile.edit');
    }
}
