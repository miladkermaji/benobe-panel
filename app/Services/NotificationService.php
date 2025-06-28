<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function sendOtpNotification($mobile, $otpCode)
    {
        try {
            // ارسال اعلان به دستگاه کاربر
            $this->sendPushNotification($mobile, [
                'title' => 'کد تایید ورود',
                'body' => "کد تایید شما: $otpCode",
                'data' => [
                    'type' => 'otp',
                    'code' => $otpCode
                ]
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Error sending OTP notification: ' . $e->getMessage());
            return false;
        }
    }

    private function sendPushNotification($mobile, $data)
    {
        // اینجا می‌توانید از خدماتی مختلف پوش نوتیفیکیشن استفاده کنید
        // مثلاً Firebase Cloud Messaging (FCM)
        // یا خدماتی دیگر مانند OneSignal

        // مثال استفاده از FCM:
        /*
        $fcm = new \Kreait\Firebase\Factory();
        $messaging = $fcm->createMessaging();

        $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $deviceToken)
            ->withNotification($data['title'], $data['body'])
            ->withData($data['data']);

        $messaging->send($message);
        */
    }
}
