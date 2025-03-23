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

    /**
     * Create a new job instance.
     */
    public function __construct(string $message, array $recipients, ?int $templateId = null, array $params = [])
    {
        $this->message = $message;
        $this->recipients = $recipients;
        $this->templateId = $templateId; // شناسه قالب پیامک (اختیاری)
        $this->params = $params; // پارامترهای اضافی برای قالب پیامک
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            foreach ($this->recipients as $recipient) {
                $user = User::where('mobile', $recipient)->first();
                $userFullName = $user ? ($user->first_name . ' ' . $user->last_name) : 'کاربر گرامی';

                // اگر templateId داریم، از قالب استفاده می‌کنیم
                if ($this->templateId) {
                    $smsService = new MessageService(
                        SmsService::create(
                            $this->templateId,
                            $recipient,
                            array_merge([$userFullName], $this->params)
                        )
                    );
                } else {
                    // اگر templateId نداریم، پیام معمولی ارسال می‌کنیم
                    $smsService = new MessageService(
                        SmsService::createMessage(
                            $this->message,
                            [$recipient]
                        )
                    );
                }

                $smsService->send();

                Log::info('پیامک با موفقیت ارسال شد', [
                    'recipient' => $recipient,
                    'message' => $this->message,
                    'template_id' => $this->templateId,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('خطا در ارسال پیامک', [
                'recipients' => $this->recipients,
                'message' => $this->message,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
