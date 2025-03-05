<?php

namespace Modules\SendOtp\App\Http\Services\SMS;

use Modules\SendOtp\App\Http\Interfaces\SmsDriverInterface;

class FarazSMSDriver implements SmsDriverInterface
{
 public function send($otpId, $parameters, $senderNumber, $recipientNumbers)
 {
  $url = "https://ippanel.com/api/v1/sms/send/webservice/single";
  $data = [
   'username' => env('FARAZSMS_USERNAME', ''),
   'password' => env('FARAZSMS_PASSWORD', ''),
   'from' => $senderNumber,
   'to' => $recipientNumbers,
   'text' => $parameters['message'] ?? 'کد تأیید: ' . ($parameters['code'] ?? ''),
  ];

  $curl = curl_init();
  curl_setopt_array($curl, [
   CURLOPT_URL => $url,
   CURLOPT_RETURNTRANSFER => true,
   CURLOPT_POST => true,
   CURLOPT_POSTFIELDS => http_build_query($data),
  ]);

  $response = curl_exec($curl);
  curl_close($curl);

  return $response;
 }
}