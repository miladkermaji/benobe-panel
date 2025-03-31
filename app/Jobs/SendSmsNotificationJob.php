<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Modules\SendOtp\App\Http\Services\MessageService;
use Modules\SendOtp\App\Http\Services\SMS\SmsService;
use Modules\SendOtp\App\Models\SmsGateway;
use App\Models\User;
use Illuminate\Support\Facades\Log;

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

    public function __construct($message, $recipients, $templateId = null, $params = [], $sendDateTime = null)
    {
        $this->message = $message;
        $this->recipients = $recipients;
        $this->templateId = $templateId;
        $this->params = $params;
        $this->sendDateTime = $sendDateTime;
    }

    public function handle()
    {
        try {
            $chunks = array_chunk($this->recipients, 10);
            $delay = 0;

            $activeGateway = SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';

            foreach ($chunks as $chunk) {
                foreach ($chunk as $recipient) {
                    $user = User::where('mobile', $recipient)->first();
                    $userFullName = $user ? ($user->first_name . ' ' . $user->last_name) : 'کاربر گرامی';

                    // تشخیص نوع پیام (OTP یا معمولی)
                    $isOtp = $this->templateId && !empty($this->params);

                    if ($isOtp) {
                        // ارسال به صورت OTP
                        $smsService = new MessageService(
                            SmsService::createOtp(
                                $this->params,
                                [$recipient],
                                $this->templateId,
                                $this->sendDateTime
                            )
                        );
                    } else {
                        // ارسال پیام معمولی
                        $smsService = new MessageService(
                            SmsService::createMessage(
                                $this->message,
                                [$recipient],
                                null,
                                $this->sendDateTime
                            )
                        );
                    }

                    $response = $smsService->send();

                    Log::info('پیامک ارسال شد', [
                        'recipient' => $recipient,
                        'type' => $isOtp ? 'OTP' : 'معمولی',
                        'template_id' => $this->templateId,
                        'gateway' => $gatewayName,
                        'response' => $response,
                    ]);
                }
                $delay += 5;
            }
        } catch (\Exception $e) {
            Log::error('خطا در ارسال پیامک', [
                'recipients' => $this->recipients,
                'message' => $this->message,
                'template_id' => $this->templateId,
                'error' => $e->getMessage(),
                'stack' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
