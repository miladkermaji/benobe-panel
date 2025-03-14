<?php
namespace Modules\SendOtp\App\Http\Interfaces;

interface SmsDriverInterface
{
    // متد فعلی برای OTP
    public function send($otpId, $parameters, $senderNumber, $recipientNumbers);

    // متد جدید برای پیامک معمولی
    public function sendMessage($message, $senderNumber, $recipientNumbers);
}
