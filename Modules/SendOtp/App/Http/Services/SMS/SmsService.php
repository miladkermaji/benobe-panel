<?php

namespace Modules\SendOtp\App\Http\Services\SMS;

use Modules\SendOtp\App\Http\Interfaces\MessageInterface;

class SmsService implements MessageInterface
{
 protected $otpId;
 protected $parameters;
 protected $senderNumber;
 protected $recipientNumbers;

 public static function create($otpId, $newMobile, $parameters)
 {
  $smsService = new self();
  $smsService->setSenderNumber(env('SMS_SENDER_NUMBER', '5000309180607211'));
  $smsService->setOtpId($otpId);
  $smsService->setParameters($parameters);
  $smsService->setRecipientNumbers([$newMobile]);
  return $smsService;
 }

 public function fire()
 {
  // نیازی به این متد نیست چون ارسال توسط MessageService انجام می‌شه
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
}