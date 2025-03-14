<?php
namespace Modules\SendOtp\App\Http\Services\SMS;

use Modules\SendOtp\App\Http\Interfaces\SmsDriverInterface;

class KavenegarDriver implements SmsDriverInterface
{
    public function send($otpId, $parameters, $senderNumber, $recipientNumbers)
    {
        $apiKey = env('KAVENEGAR_API_KEY', '');
        $url    = "https://api.kavenegar.com/v1/{$apiKey}/sms/send.json";
        $data   = [
            'receptor' => implode(',', $recipientNumbers),
            'sender'   => $senderNumber,
            'message'  => $parameters['message'] ?? 'کد تأیید: ' . ($parameters['code'] ?? ''),
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url . '?' . http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function sendMessage($message, $senderNumber, $recipientNumbers)
    {
        $apiKey = env('KAVENEGAR_API_KEY', '');
        $url    = "https://api.kavenegar.com/v1/{$apiKey}/sms/send.json";
        $data   = [
            'receptor' => implode(',', $recipientNumbers),
            'sender'   => $senderNumber,
            'message'  => $message,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url . '?' . http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
