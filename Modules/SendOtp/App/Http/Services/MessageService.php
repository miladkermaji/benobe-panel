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

    return $driver->sendMessage(
        $this->message->getMessage(),
        $this->message->getSenderNumber(),
        $this->message->getRecipientNumbers()
    );
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
