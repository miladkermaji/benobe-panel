<?php

namespace Modules\SendOtp\App\Http\Services\SMS;

use Modules\SendOtp\App\Http\Interfaces\MessageInterface;
use Modules\SendOtp\App\Models\SmsGateway;

class SmsService implements MessageInterface
{
    protected $otpId;
    protected $parameters;
    protected $senderNumber;
    protected $recipientNumbers;
    protected $message;
    protected $sendDateTime;

    public function __construct(array $data = [])
    {
        if (isset($data['senderNumber'])) {
            $this->senderNumber = $data['senderNumber'];
        }
        if (isset($data['messageBodies'])) {
            $this->message = $data['messageBodies'];
        }
        if (isset($data['recipientNumbers'])) {
            $this->recipientNumbers = $data['recipientNumbers'];
        }
        if (isset($data['sendDateTime'])) {
            $this->sendDateTime = $data['sendDateTime'];
        }
    }

    public static function create($otpId, $newMobile, $parameters)
    {
        $smsService = new self();

        $activeGateway = SmsGateway::where('is_active', true)->first();
        $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';

        $senderNumber = match ($gatewayName) {
            'kavenegar' => env('KAVENEGAR_SENDER_NUMBER', '2000990007700'),
            'pishgamrayan' => env('SMS_SENDER_NUMBER', '5000309180607211'),
            'farazsms' => env('FARAZSMS_SENDER_NUMBER', ''),
            'mellipayamak' => env('MELLIPAYAMAK_SENDER_NUMBER', ''),
            'payamito' => env('PAYAMITO_SENDER_NUMBER', ''),
            default => env('SMS_SENDER_NUMBER', '5000309180607211'),
        };

        // اگه پیشگام رایان باشه، از قالب استفاده کن
        if ($gatewayName === 'pishgamrayan') {
            $formattedParameters = $parameters; // پارامترها برای پیشگام رایان همون شکلی که هستن
            $smsService->setOtpId($otpId); // تنظیم otpId برای پیشگام رایان
        } else {
            // برای بقیه پنل‌ها، فقط کد رو به عنوان پیام ساده بفرست
            $formattedParameters = ['message' => $parameters[0] ?? '']; // پیام ساده
            $smsService->setOtpId(null); // بدون otpId
        }

        $smsService->setSenderNumber($senderNumber);
        $smsService->setParameters($formattedParameters);
        $smsService->setRecipientNumbers([$newMobile]);
        return $smsService;
    }

  public static function createMessage($message, $recipients, $senderNumber = null, $sendDateTime = null)
{
    $activeGateway = SmsGateway::where('is_active', true)->first();
    $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';

    $senderNumber = $senderNumber ?? match ($gatewayName) {
        'kavenegar' => env('KAVENEGAR_SENDER_NUMBER', '2000990007700'),
        'pishgamrayan' => env('SMS_SENDER_NUMBER', '5000309180607211'),
        'farazsms' => env('FARAZSMS_SENDER_NUMBER', ''),
        'mellipayamak' => env('MELLIPAYAMAK_SENDER_NUMBER', ''),
        'payamito' => env('PAYAMITO_SENDER_NUMBER', ''),
        default => env('SMS_SENDER_NUMBER', '5000309180607211'),
    };

    // اگه sendDateTime نبود یا فرمتش اشتباه بود، زمان فعلی رو به شمسی بذار
    if (!$sendDateTime) {
        $sendDateTime = \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i:s');
    }

    $smsService = new self();
    $smsService->setMessage($message);
    $smsService->setRecipientNumbers($recipients);
    $smsService->setSenderNumber($senderNumber);
    $smsService->sendDateTime = $sendDateTime;

    return $smsService;
}

    public function fire()
    {
    }

    public function getOtpId()
    {
        return $this->otpId;
    }
    public function setOtpId($otpId)
    {
        $this->otpId = $otpId;
    }

    public function getParameters()
    {
        return $this->parameters;
    }
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    public function getSenderNumber()
    {
        return $this->senderNumber;
    }
    public function setSenderNumber($senderNumber)
    {
        $this->senderNumber = $senderNumber;
    }

    public function getRecipientNumbers()
    {
        return $this->recipientNumbers;
    }
    public function setRecipientNumbers($recipientNumbers)
    {
        $this->recipientNumbers = $recipientNumbers;
    }

    public function getMessage()
    {
        return $this->message;
    }
    public function setMessage($message)
    {
        $this->message = $message;
    }
}
