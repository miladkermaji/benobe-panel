<?php

namespace App\Http\Controllers\Mc\Panel\Comunication;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Mc\Controller;

class DoctorSendMessageController extends Controller
{
    public function index(Request $request)
    {

        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');


        $messages = SmsTemplate::with('user')->latest()->get();



        return view('mc.panel.comunication.send-message', compact('messages'));
    }
}
