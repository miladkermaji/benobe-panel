<?php

namespace App\Http\Controllers\Mc\Panel\Comunication;

use App\Models\SmsTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Mc\Controller;
use App\Traits\HasSelectedDoctor;

class DoctorSendMessageController extends Controller
{
    use HasSelectedDoctor;
    public function index(Request $request)
    {

        $doctorId = $this->getSelectedDoctorId() ;
        $clinicId = ($request->input('selectedClinicId') === 'default') ? null : $request->input('selectedClinicId');


        $messages = SmsTemplate::with('user')->latest()->get();



        return view('mc.panel.comunication.send-message', compact('messages'));
    }
}
