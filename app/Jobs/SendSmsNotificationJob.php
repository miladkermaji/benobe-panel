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

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, array $recipients, ?int $templateId = null, array $params = [], ?string $sendDateTime = null)
    {
        $this->message = $message;
        $this->recipients = $recipients;
        $this->templateId = $templateId; // شناسه قالب پیامک (اختیاری)
        $this->params = $params; // پارامترهای اضافی برای قالب پیامک
        $this->sendDateTime = $sendDateTime ?? now()->format('Y-m-d\TH:i:s'); // زمان ارسال پیش‌فرض
    }

    /**
     * Execute the job.
     */
  public function handle()
{
    try {
        $chunks = array_chunk($this->recipients, 10);
        $delay = 0;

        // تبدیل sendDateTime از میلادی به شمسی
        $jalaliSendDateTime = \Morilog\Jalali\Jalalian::fromDateTime(
            \Carbon\Carbon::parse($this->sendDateTime)
        )->format('Y/m/d H:i:s');

        foreach ($chunks as $chunk) {
            foreach ($chunk as $recipient) {
                $user = User::where('mobile', $recipient)->first();
                $userFullName = $user ? ($user->first_name . ' ' . $user->last_name) : 'کاربر گرامی';

                $smsService = new MessageService(
                    SmsService::createMessage(
                        $this->message,
                        [$recipient],
                        null,
                        $jalaliSendDateTime // فرمت شمسی رو پاس می‌دیم
                    )
                );

                $smsService->send();
            }
            $delay += 5;
        }
    } catch (\Exception $e) {
        Log::error('خطا در ارسال پیامک', [
            'recipients' => $this->recipients,
            'message' => $this->message,
            'template_id' => $this->templateId,
            'error' => $e->getMessage(),
        ]);
        throw $e;
    }
}
}
