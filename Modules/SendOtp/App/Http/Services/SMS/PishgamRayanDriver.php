<?php
namespace Modules\SendOtp\App\Http\Services\SMS;

use Modules\SendOtp\App\Http\Interfaces\SmsDriverInterface;

class PishgamRayanDriver implements SmsDriverInterface
{
    public function send($otpId, $parameters, $senderNumber, $recipientNumbers)
    {
        $url  = "https://smsapi.pishgamrayan.com/Messages/SendOtp";
        $data = [
            'otpId'            => $otpId,
            'parameters'       => $parameters,
            'senderNumber'     => $senderNumber,
            'recipientNumbers' => $recipientNumbers,
        ];

        $headers = [
            'Authorization: ' . env('SMS_AUTH_KEY', 'hP0qkpJZriM8PyVujTepFgVhnhlpv05wuVtRYm0v2I4='),
            'Content-Type: application/json',
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    public function sendMessage($message, $senderNumber, $recipientNumbers)
    {
        $url  = "https://smsapi.pishgamrayan.com/Messages/Send";
        $data = [
            'message'          => $message,
            'senderNumber'     => $senderNumber,
            'recipientNumbers' => $recipientNumbers,
        ];

        $headers = [
            'Authorization: ' . env('SMS_AUTH_KEY', 'hP0qkpJZriM8PyVujTepFgVhnhlpv05wuVtRYm0v2I4='),
            'Content-Type: application/json',
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_HTTPHEADER     => $headers,
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
