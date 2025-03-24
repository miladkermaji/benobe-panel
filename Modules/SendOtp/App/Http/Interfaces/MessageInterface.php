<?php

namespace Modules\SendOtp\App\Http\Interfaces;

interface MessageInterface
{
 public function fire();

 public function getOtpId();

 public function getParameters();

 public function getSenderNumber();
 public function getMessage();

 public function getRecipientNumbers();

}