<?php

namespace App\Http\Controllers\Dr\Panel\Comunication;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;

class DoctorSendMessageController extends Controller
{
    public function index(Request $request)
    {

        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');


        $messages = SmsTemplate::with('user')->latest()->get();

       

        return view('dr.panel.comunication.send-message',compact('messages'));
    }
}
