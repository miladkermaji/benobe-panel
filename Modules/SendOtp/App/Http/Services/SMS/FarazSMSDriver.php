<?php
namespace Modules\SendOtp\App\Http\Services\SMS;

use Modules\SendOtp\App\Http\Interfaces\SmsDriverInterface;

class FarazSMSDriver implements SmsDriverInterface
{
    public function send($otpId, $parameters, $senderNumber, $recipientNumbers)
    {
        $url = "https://ippanel.com/api/v1/sms/send/webservice/single";
        $apiKey = env('FARAZSMS_API_KEY', '');
        if (empty($apiKey)) {
            \Log::error('FarazSMS API Key is missing');
            return json_encode(['error' => 'API Key is missing']);
        }

        $message = $parameters['message'] ?? 'کد تأیید: ' . ($parameters['code'] ?? '');
        $recipients = is_array($recipientNumbers) ? $recipientNumbers : [$recipientNumbers];

        $data = [
            'from' => $senderNumber, // مطمئن شو +983000505 باشه
            'to' => $recipients,
            'text' => $message,
        ];

        $headers = [
            "Authorization: AccessToken {$apiKey}",
            'Content-Type: application/json',
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true, // اگه ریدایرکت شد، دنبالش بره
        ]);

        $response = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            \Log::error('FarazSMS cURL error', ['error' => $error]);
        }

        curl_close($curl);

        \Log::info('FarazSMS send request', [
            'url' => $url,
            'data' => $data,
            'headers' => $headers,
            'http_code' => $httpCode,
            'response_headers' => $headers,
            'response_body' => $body,
        ]);

        return $body;
    }

    public function sendMessage($message, $senderNumber, $recipientNumbers)
    {
        // مشابه بالا
        $url = "https://ippanel.com/api/v1/sms/send/webservice/single";
        $apiKey = env('FARAZSMS_API_KEY', '');
        if (empty($apiKey)) {
            \Log::error('FarazSMS API Key is missing');
            return json_encode(['error' => 'API Key is missing']);
        }

        $recipients = is_array($recipientNumbers) ? $recipientNumbers : [$recipientNumbers];

        $data = [
            'from' => $senderNumber,
            'to' => $recipients,
            'text' => $message,
        ];

        $headers = [
            "Authorization: AccessToken {$apiKey}",
            'Content-Type: application/json',
        ];

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_VERBOSE => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
        ]);

        $response = curl_exec($curl);
        $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $headers = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);

        if (curl_errno($curl)) {
            $error = curl_error($curl);
            \Log::error('FarazSMS cURL error', ['error' => $error]);
        }

        curl_close($curl);

        \Log::info('FarazSMS sendMessage request', [
            'url' => $url,
            'data' => $data,
            'headers' => $headers,
            'http_code' => $httpCode,
            'response_headers' => $headers,
            'response_body' => $body,
        ]);

        return $body;
    }
}