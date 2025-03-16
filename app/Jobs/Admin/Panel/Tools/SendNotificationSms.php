<?php
namespace App\Jobs\Admin\Panel\Tools;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use Modules\SendOtp\App\Models\SmsGateway;
use Morilog\Jalali\Jalalian;

class SendNotificationSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $recipientNumbers;
    protected $sendDateTime;

    public $tries = 3;

    public function __construct($message, $recipientNumbers, $sendDateTime = null)
    {
        $this->message          = $message;
        $this->recipientNumbers = is_array($recipientNumbers) ? $recipientNumbers : [$recipientNumbers];
        $this->sendDateTime     = $sendDateTime;
    }

  public function handle()
{
    Log::info('شروع اجرای Job ارسال پیامک', [
        'message'      => $this->message,
        'recipients'   => $this->recipientNumbers,
        'sendDateTime' => $this->sendDateTime,
    ]);

    try {
        $activeGateway = SmsGateway::where('is_active', true)->first();
        if (!$activeGateway) {
            throw new \Exception('هیچ پنل پیامکی فعال نیست');
        }

        Log::info('پنل پیامکی فعال', ['gateway' => $activeGateway->name]);

        $apiKey = $this->getApiKey($activeGateway->name);
        $senderNumber = $this->getSenderNumber($activeGateway->name);

        Log::info('API Key و شماره فرستنده', [
            'apiKey'       => $apiKey,
            'senderNumber' => $senderNumber,
        ]);

        if (empty($apiKey)) {
            throw new \Exception("API Key برای پنل {$activeGateway->name} تنظیم نشده است");
        }
        if (empty($senderNumber)) {
            throw new \Exception("شماره فرستنده برای پنل {$activeGateway->name} تنظیم نشده است");
        }

        $sendDateTimeMiladi = now()->toDateTimeString();
        if ($this->sendDateTime) {
            $sendDateTimeMiladi = Jalalian::fromFormat('Y/m/d H:i:s', $this->sendDateTime)
                ->toCarbon()
                ->toDateTimeString();
        }

        $smsService = SmsService::createMessage(
            $this->message,
            $this->recipientNumbers,
            $senderNumber,
            $sendDateTimeMiladi
        );
        $messageService = new MessageService($smsService);
        $response = $messageService->send();

        Log::info('نتیجه ارسال پیامک', [
            'response'           => $response,
            'recipients'         => $this->recipientNumbers,
            'sendDateTimeMiladi' => $sendDateTimeMiladi,
        ]);

        $responseData = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($responseData)) {
            Log::error('پاسخ API قابل تجزیه به JSON نیست یا نامعتبر است', [
                'response'   => $response,
                'recipients' => $this->recipientNumbers,
            ]);
            $this->release(10);
            return;
        }

        if ($activeGateway->name === 'kavenegar') {
            // ساختار پاسخ کاوه‌نگار رو چک می‌کنیم
            if (isset($responseData[0]['messageid']) && isset($responseData[0]['status'])) {
                $status = $responseData[0]['status'];
                if (in_array($status, [1, 2, 4, 5, 6, 10])) { // وضعیت‌های موفق یا در حال پردازش
                    Log::info('ارسال پیامک موفق بود', [
                        'messageId'  => $responseData[0]['messageid'],
                        'recipients' => $this->recipientNumbers,
                        'status'     => $responseData[0]['statustext'],
                    ]);
                } else {
                    Log::warning('ارسال پیامک ناموفق بود', [
                        'status'     => $responseData[0]['status'],
                        'statustext' => $responseData[0]['statustext'] ?? 'خطای نامشخص',
                        'recipients' => $this->recipientNumbers,
                    ]);
                    $this->release(10);
                }
            } else {
                Log::error('پاسخ کاوه‌نگار نامعتبر است', [
                    'response'   => $responseData,
                    'recipients' => $this->recipientNumbers,
                ]);
                $this->release(10);
            }
        } else {
            Log::info('پاسخ پنل غیرکاوه‌نگار', [
                'response'   => $responseData,
                'recipients' => $this->recipientNumbers,
            ]);
        }
    } catch (\Exception $e) {
        Log::error('خطا در ارسال پیامک: ' . $e->getMessage(), [
            'message'    => $this->message,
            'recipients' => $this->recipientNumbers,
            'stack'      => $e->getTraceAsString(),
        ]);
        $this->release(10);
    }

    Log::info('پایان اجرای Job');
}

    /**
     * گرفتن API Key بر اساس پنل فعال
     */
    protected function getApiKey($gatewayName)
    {
        return match ($gatewayName) {
            'kavenegar'    => env('KAVENEGAR_API_KEY'),
            'pishgamrayan' => env('SMS_AUTH_KEY'),
            'farazsms'     => env('FARAZSMS_API_KEY'),
            'mellipayamak' => env('MELLIPAYAMAK_USERNAME'), // برای ملی پیامک ممکنه فرمت متفاوتی نیاز باشه
            'payamito'     => env('PAYAMITO_USERNAME'),
            default        => null,
        };
    }

    /**
     * گرفتن شماره فرستنده بر اساس پنل فعال
     */
    protected function getSenderNumber($gatewayName)
    {
        return match ($gatewayName) {
            'kavenegar'    => env('KAVENEGAR_SENDER_NUMBER', '2000990007700'),
            'pishgamrayan' => env('SMS_SENDER_NUMBER', '5000309180607211'),
            'farazsms'     => env('FARAZSMS_SENDER_NUMBER', '+983000505'),
            'mellipayamak' => env('MELLIPAYAMAK_SENDER_NUMBER'),
            'payamito'     => env('PAYAMITO_SENDER_NUMBER'),
            default        => null,
        };
    }
}