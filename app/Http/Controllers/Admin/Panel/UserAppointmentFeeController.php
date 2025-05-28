<?php

namespace App\Http\Controllers\Admin\Panel;

use App\Http\Controllers\Controller;
use App\Models\UserAppointmentFee;
use Illuminate\Http\Request;

class UserAppointmentFeeController extends Controller
{
    public function index()
    {
        $fees = UserAppointmentFee::latest()->paginate(10);
        return view('admin.panel.user-appointment-fees.index', compact('fees'));
    }

    public function create()
    {
        return view('admin.panel.user-appointment-fees.create');
    }



    public function edit(UserAppointmentFee $userAppointmentFee)
    {
        return view('admin.panel.user-appointment-fees.edit', compact('userAppointmentFee'));
    }

   
} 