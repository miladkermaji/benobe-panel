<?php
namespace Modules\SendOtp\App\Http\Services\SMS;

use Modules\SendOtp\App\Http\Interfaces\MessageInterface;

class SmsService implements MessageInterface
{
    protected $otpId;
    protected $parameters;
    protected $senderNumber;
    protected $recipientNumbers;
    protected $message; // برای پیام معمولی

    // متد فعلی برای OTP
    public static function create($otpId, $newMobile, $parameters)
    {
        $smsService = new self();
        $smsService->setSenderNumber(env('SMS_SENDER_NUMBER', '5000309180607211'));
        $smsService->setOtpId($otpId);
        $smsService->setParameters($parameters);
        $smsService->setRecipientNumbers([$newMobile]);
        return $smsService;
    }

    // متد جدید برای پیام معمولی
    public static function createMessage($message, $recipients, $senderNumber = null, $sendDateTime = null)
    {
        $senderNumber = $senderNumber ?? env('SMS_SENDER_NUMBER', '5000309180607211');
        $sendDateTime = $sendDateTime ?? now()->format('Y-m-d\TH:i:s');

        $smsService = new self();
        $smsService->setMessage($message);
        $smsService->setSenderNumber($senderNumber);
        $smsService->setRecipientNumbers(is_array($recipients) ? $recipients : [$recipients]);

        return $smsService;
    }

    public function fire()
    {
        // بعداً توی MessageService استفاده می‌شه
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
