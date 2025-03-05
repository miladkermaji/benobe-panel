<?php

namespace Modules\SendOtp\database\seeders;

use Illuminate\Database\Seeder;
use Modules\SendOtp\App\Models\SmsGateway;

class SmsGatewaySeeder extends Seeder
{
 public function run()
 {
  $gateways = [
   [
    'name' => 'pishgamrayan',
    'title' => 'پیشگام رایان',
    'is_active' => true,
    'settings' => json_encode([
     'auth_key' => env('SMS_AUTH_KEY', 'hP0qkpJZriM8PyVujTepFgVhnhlpv05wuVtRYm0v2I4='),
     'otp_id' => env('SMS_OTP_ID', '96'),
     'sender_number' => env('SMS_SENDER_NUMBER', '5000309180607211')
    ])
   ],
   [
    'name' => 'farazsms',
    'title' => 'فراز اس‌ام‌اس',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('FARAZSMS_USERNAME', ''),
     'password' => env('FARAZSMS_PASSWORD', ''),
     'sender_number' => env('FARAZSMS_SENDER_NUMBER', '')
    ])
   ],
   [
    'name' => 'mellipayamak',
    'title' => 'ملی پیامک',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('MELLIPAYAMAK_USERNAME', ''),
     'password' => env('MELLIPAYAMAK_PASSWORD', ''),
     'sender_number' => env('MELLIPAYAMAK_SENDER_NUMBER', '')
    ])
   ],
   [
    'name' => 'kavenegar',
    'title' => 'کاوه‌نگار',
    'is_active' => false,
    'settings' => json_encode([
     'api_key' => env('KAVENEGAR_API_KEY', ''),
     'sender_number' => env('KAVENEGAR_SENDER_NUMBER', '')
    ])
   ],
   [
    'name' => 'payamito',
    'title' => 'پیامیتو',
    'is_active' => false,
    'settings' => json_encode([
     'username' => env('PAYAMITO_USERNAME', ''),
     'password' => env('PAYAMITO_PASSWORD', ''),
     'sender_number' => env('PAYAMITO_SENDER_NUMBER', '')
    ])
   ],
  ];

  foreach ($gateways as $gateway) {
   SmsGateway::updateOrCreate(['name' => $gateway['name']], $gateway);
  }
 }
}