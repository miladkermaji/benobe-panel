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

class SendNotificationSms implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $message;
    protected $recipientNumbers;

    /**
     * Create a new job instance.
     */
    public function __construct($message, $recipientNumbers)
    {
        $this->message          = $message;
        $this->recipientNumbers = $recipientNumbers;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        try {
            $smsService     = SmsService::createMessage($this->message, $this->recipientNumbers);
            $messageService = new MessageService($smsService);
            $response       = $messageService->send();

            if ($response === false) { // فرض می‌کنیم false یعنی خطا
                Log::error('خطا در ارسال پیامک از صف', [
                    'message'    => $this->message,
                    'recipients' => $this->recipientNumbers,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('خطای غیرمنتظره در ارسال پیامک از صف: ' . $e->getMessage(), [
                'message'    => $this->message,
                'recipients' => $this->recipientNumbers,
            ]);
        }
    }
}
