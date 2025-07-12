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

            $jalaliSendDateTime = $this->sendDateTime
                ? \Morilog\Jalali\Jalalian::fromDateTime(
                    \Carbon\Carbon::parse($this->sendDateTime)
                )->format('Y/m/d H:i:s')
                : \Morilog\Jalali\Jalalian::now()->format('Y/m/d H:i:s');

            $activeGateway = SmsGateway::where('is_active', true)->first();
            $gatewayName = $activeGateway ? $activeGateway->name : 'pishgamrayan';

            foreach ($chunks as $chunk) {
                foreach ($chunk as $recipient) {
                    $user = User::where('mobile', $recipient)->first();
                    $userFullName = $user ? ($user->first_name . ' ' . $user->last_name) : 'کاربر گرامی';

                    // Create SmsService directly
                    $smsService = SmsService::createMessage(
                        $this->message,
                        [$recipient],
                        null,
                        $jalaliSendDateTime
                    );

                    // Set template ID and parameters directly on SmsService
                    if ($gatewayName === 'pishgamrayan' && $this->templateId) {
                        $smsService->setOtpId($this->templateId);
                        $smsService->setParameters($this->params);
                    } else {
                        $smsService->setOtpId(null);
                        $smsService->setParameters([]);
                    }

                    // Create MessageService and send
                    $messageService = new MessageService($smsService);
                    $response = $messageService->send();

                    Log::info('پیامک با موفقیت ارسال شد', [
                        'recipient' => $recipient,
                        'message' => $this->message,
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
