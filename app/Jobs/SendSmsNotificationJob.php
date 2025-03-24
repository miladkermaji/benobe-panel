<?php

namespace App\Jobs;

use App\Models\User;
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

class SendSmsNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected $message;
    protected $recipients;
    protected $templateId;
    protected $params;
    protected $sendDateTime;

    public $tries = 3; // تعداد تلاش مجدد در صورت خطا

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, array $recipients, ?int $templateId = null, array $params = [], ?string $sendDateTime = null)
    {
        $this->message = $message;
        $this->recipients = $recipients;
        $this->templateId = $templateId; // شناسه قالب پیامک (اختیاری)
        $this->params = $params; // پارامترهای اضافی برای قالب پیامک
        $this->sendDateTime = $sendDateTime;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        Log::info('شروع اجرای جاب ارسال پیامک', [
            'message' => $this->message,
            'recipients' => $this->recipients,
            'template_id' => $this->templateId,
            'params' => $this->params,
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
                'apiKey' => $apiKey,
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

            $chunks = array_chunk($this->recipients, 10); // ارسال گروهی به صورت تکه‌های 10 تایی

            foreach ($chunks as $chunk) {
                if ($this->templateId) {
                    // ارسال پیام با قالب
                    foreach ($chunk as $recipient) {
                        $user = User::where('mobile', $recipient)->first();
                        $userFullName = $user ? ($user->first_name . ' ' . $user->last_name) : 'کاربر گرامی';

                        $smsService = new MessageService(
                            SmsService::create(
                                $this->templateId,
                                $recipient,
                                array_merge([$userFullName], $this->params),
                                $senderNumber,
                                $sendDateTimeMiladi
                            )
                        );
                        $response = $smsService->send();

                        $this->handleResponse($response, $recipient, $activeGateway->name);
                    }
                } else {
                    // ارسال پیام معمولی
                    $smsService = new MessageService(
                        SmsService::createMessage(
                            $this->message,
                            $chunk,
                            $senderNumber,
                            $sendDateTimeMiladi
                        )
                    );
                    $response = $smsService->send();

                    $this->handleResponse($response, $chunk, $activeGateway->name);
                }
            }
        } catch (\Exception $e) {
            Log::error('خطا در ارسال پیامک', [
                'recipients' => $this->recipients,
                'message' => $this->message,
                'template_id' => $this->templateId,
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            $this->release(10); // تلاش مجدد بعد از 10 ثانیه
        }

        Log::info('پایان اجرای جاب');
    }

    /**
     * مدیریت پاسخ API و بررسی موفقیت ارسال
     */
    protected function handleResponse($response, $recipients, $gatewayName)
    {
        $responseData = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($responseData)) {
            Log::error('پاسخ API قابل تجزیه به JSON نیست یا نامعتبر است', [
                'response' => $response,
                'recipients' => $recipients,
            ]);
            $this->release(10);
            return;
        }

        if ($gatewayName === 'kavenegar') {
            foreach ($responseData as $result) {
                if (isset($result['messageid']) && isset($result['status'])) {
                    $status = $result['status'];
                    if (in_array($status, [1, 2, 4, 5, 6, 10])) { // وضعیت‌های موفق یا در حال پردازش
                        Log::info('پیامک با موفقیت ارسال شد', [
                            'messageId' => $result['messageid'],
                            'recipient' => $result['receptor'],
                            'status' => $result['statustext'],
                            'message' => $this->message,
                            'template_id' => $this->templateId,
                        ]);
                    } else {
                        Log::warning('ارسال پیامک ناموفق بود', [
                            'status' => $result['status'],
                            'statustext' => $result['statustext'] ?? 'خطای نامشخص',
                            'recipient' => $result['receptor'],
                        ]);
                        $this->release(10);
                    }
                } else {
                    Log::error('پاسخ کاوه‌نگار نامعتبر است', [
                        'response' => $responseData,
                        'recipients' => $recipients,
                    ]);
                    $this->release(10);
                }
            }
        } else {
            Log::info('پاسخ پنل غیرکاوه‌نگار', [
                'response' => $responseData,
                'recipients' => $recipients,
            ]);
        }
    }

    /**
     * گرفتن API Key بر اساس پنل فعال
     */
    protected function getApiKey($gatewayName)
    {
        return match ($gatewayName) {
            'kavenegar' => env('KAVENEGAR_API_KEY'),
            'pishgamrayan' => env('SMS_AUTH_KEY'),
            'farazsms' => env('FARAZSMS_API_KEY'),
            'mellipayamak' => env('MELLIPAYAMAK_USERNAME'),
            'payamito' => env('PAYAMITO_USERNAME'),
            default => null,
        };
    }

    /**
     * گرفتن شماره فرستنده بر اساس پنل فعال
     */
    protected function getSenderNumber($gatewayName)
    {
        return match ($gatewayName) {
            'kavenegar' => env('KAVENEGAR_SENDER_NUMBER', '2000990007700'),
            'pishgamrayan' => env('SMS_SENDER_NUMBER', '5000309180607211'),
            'farazsms' => env('FARAZSMS_SENDER_NUMBER', '+983000505'),
            'mellipayamak' => env('MELLIPAYAMAK_SENDER_NUMBER'),
            'payamito' => env('PAYAMITO_SENDER_NUMBER'),
            default => null,
        };
    }
}
