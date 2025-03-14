<?php
namespace Modules\SendOtp\App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use Nwidart\Modules\Routing\Controller;

class SendOtpController extends Controller
{
    public function index()
    {
        return view('sendotp::index');
    }

    // متد جدید برای ارسال پیام معمولی
    public function sendMessage(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
            'mobile'  => 'required|string',
        ]);

        $smsService     = SmsService::createMessage($request->message, $request->mobile);
        $messageService = new MessageService($smsService);
        $response       = $messageService->send();

        return response()->json(['status' => 'success', 'response' => $response]);
    }
}
