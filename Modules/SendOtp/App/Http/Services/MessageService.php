<?php

namespace Modules\SendOtp\App\Http\Services;

use Illuminate\Support\Facades\Log;
use Modules\SendOtp\App\Models\SmsGateway;
use Modules\SendOtp\App\Http\Interfaces\MessageInterface;
use Modules\SendOtp\App\Http\Services\SMS\FarazSMSDriver;
use Modules\SendOtp\App\Http\Services\SMS\PayamitoDriver;
use Modules\SendOtp\App\Http\Services\SMS\KavenegarDriver;
use Modules\SendOtp\App\Http\Services\SMS\MelliPayamakDriver;
use Modules\SendOtp\App\Http\Services\SMS\PishgamRayanDriver;

class MessageService
{
    public $message;

    public function __construct(MessageInterface $message)
    {
        $this->message = $message;
    }

  public function send()
{
    $activeGateway = SmsGateway::where('is_active', true)->first();
    $driver = $activeGateway ? $this->getDriver($activeGateway->name) : new PishgamRayanDriver();

    Log::info('بررسی مقادیر قبل از ارسال', [
        'otpId' => $this->message->getOtpId(),
        'message' => $this->message->getMessage(),
        'parameters' => $this->message->getParameters(),
        'senderNumber' => $this->message->getSenderNumber(),
        'recipients' => $this->message->getRecipientNumbers()
    ]);

    if ($this->message->getOtpId()) {
        return $driver->send(
            $this->message->getOtpId(),
            $this->message->getParameters(),
            $this->message->getSenderNumber(),
            $this->message->getRecipientNumbers()
        );
    }

    // اگه otpId نبود، پیام پیش‌فرض بساز
    $messageContent = $this->message->getMessage();
    if (empty($messageContent) && $this->message->getParameters()) {
        // چک کن که parameters یه آرایه ассоциатив هست یا نه
        $params = $this->message->getParameters();
        $code = $params['message'] ?? $params[0] ?? ''; // اول کلید 'message' رو چک کن، بعد ایندکس 0
        $messageContent = "کد تایید شما: $code - به نوبه";
    }

    $response = $driver->sendMessage(
        $messageContent ?: $this->message->getMessage() ?: 'پیام پیش‌فرض',
        $this->message->getSenderNumber(),
        $this->message->getRecipientNumbers()
    );

    // لاگ اضافه برای بررسی پاسخ کامل
    Log::info('پاسخ کامل کاوه‌نگار', [
        'response' => $response,
        'message_sent' => $messageContent
    ]);

    return $response;
}

    protected function getDriver($driverName)
    {
        $drivers = [
            'pishgamrayan' => PishgamRayanDriver::class,
            'farazsms'     => FarazSMSDriver::class,
            'mellipayamak' => MelliPayamakDriver::class,
            'kavenegar'    => KavenegarDriver::class,
            'payamito'     => PayamitoDriver::class,
        ];

        $driverClass = $drivers[$driverName] ?? PishgamRayanDriver::class;
        return new $driverClass();
    }
}
