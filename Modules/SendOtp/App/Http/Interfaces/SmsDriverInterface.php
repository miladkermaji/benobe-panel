<?php

namespace Modules\SendOtp\App\Http\Interfaces;

interface SmsDriverInterface
{
 public function send($otpId, $parameters, $senderNumber, $recipientNumbers);
}