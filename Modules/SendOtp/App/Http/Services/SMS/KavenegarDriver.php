<?php

namespace Modules\SendOtp\App\Http\Services\SMS;

use Kavenegar\KavenegarApi;
use Illuminate\Support\Facades\Log;
use Modules\SendOtp\App\Http\Interfaces\SmsDriverInterface;

class KavenegarDriver implements SmsDriverInterface
{
    protected $api;

 public function __construct()
{
    $apiKey = "382B443375422F4465705375364E753136496C417154513951484D6844766E35456B6C68434B4C5A726A493D"; // مقدار مستقیم
    if (empty($apiKey)) {
        Log::error('Kavenegar API Key is not set in .env');
        throw new \Exception('Kavenegar API Key is missing');
    }
    $this->api = new KavenegarApi($apiKey);
}

    public function send($otpId, $parameters, $senderNumber, $recipientNumbers)
    {
        try {
            // پیام پیش‌فرض یا از پارامترها
            $code = $parameters['code'] ?? '';
            $message = $parameters['message'] ?? "کد ورود شما: {$code} - به نوبه";
            $receptors = is_array($recipientNumbers) ? $recipientNumbers : [$recipientNumbers];

            $result = $this->api->Send($senderNumber, $receptors, $message);
            Log::info('Kavenegar OTP sent', ['result' => $result]);
            return json_encode($result);
        } catch (\Exception $e) {
            Log::error('Kavenegar OTP error', ['error' => $e->getMessage()]);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function sendMessage($message, $senderNumber, $recipientNumbers)
    {
        try {
            $receptors = is_array($recipientNumbers) ? $recipientNumbers : [$recipientNumbers];
            Log::info('درخواست ارسال به کاوه‌نگار', [
                'sender'    => $senderNumber,
                'receptors' => $receptors,
                'message'   => $message,
            ]);
            $result = $this->api->Send($senderNumber, $receptors, $message);
            Log::info('Kavenegar message sent', ['result' => $result]);
            return json_encode($result);
        } catch (\Exception $e) {
            Log::error('Kavenegar message error', ['error' => $e->getMessage()]);
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}
