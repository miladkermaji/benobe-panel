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
use Morilog\Jalali\Jalalian;

class SendNotificationSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $recipientNumbers;
    protected $sendDateTime;

    public $tries = 3; // حداکثر تلاش‌ها

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
            // تبدیل تاریخ شمسی به میلادی
            $sendDateTimeMiladi = now()->toDateTimeString();
            if ($this->sendDateTime) {
                $sendDateTimeMiladi = Jalalian::fromFormat('Y/m/d H:i:s', $this->sendDateTime)->toCarbon()->toDateTimeString();
            }

            $senderNumber = env('SMS_SENDER_NUMBER', '5000309180607211');
            $smsService   = SmsService::createMessage(
                $this->message,
                $this->recipientNumbers,
                $senderNumber,
                $sendDateTimeMiladi
            );
            $messageService = new MessageService($smsService);
            $response       = $messageService->send();

            Log::info('نتیجه ارسال پیامک', [
                'response'           => $response,
                'recipients'         => $this->recipientNumbers,
                'sendDateTimeMiladi' => $sendDateTimeMiladi,
            ]);

            $responseData = json_decode($response, true);
            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($responseData)) {
                Log::error('پاسخ API قابل تجزیه به JSON نیست یا نامعتبر است', [
                    'response'   => $response,
                    'recipients' => $this->recipientNumbers,
                ]);
            } elseif (isset($responseData['statusCode'])) {
                if ($responseData['statusCode'] == 1) {
                    Log::info('ارسال پیامک موفق بود', [
                        'messageId'  => $responseData['messageId'] ?? 'نامشخص',
                        'recipients' => $this->recipientNumbers,
                    ]);
                } else {
                    Log::warning('ارسال پیامک ناموفق بود', [
                        'statusCode' => $responseData['statusCode'],
                        'message'    => $this->message,
                        'recipients' => $this->recipientNumbers,
                    ]);
                    if ($responseData['statusCode'] == 500) {
                        $this->release(10); // تلاش مجدد بعد از 10 ثانیه
                    }
                }
            } else {
                Log::error('پاسخ API فاقد statusCode است', [
                    'response'   => $response,
                    'recipients' => $this->recipientNumbers,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('خطا در ارسال پیامک: ' . $e->getMessage(), [
                'message'    => $this->message,
                'recipients' => $this->recipientNumbers,
                'stack'      => $e->getTraceAsString(),
            ]);
            $this->release(10); // تلاش مجدد در صورت خطا
        }

        Log::info('پایان اجرای Job');
    }
}
