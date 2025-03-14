<?php
namespace App\Livewire\Admin\Panel\Tools;

use App\Jobs\Admin\Panel\Tools\SendNotificationSms;

use App\Models\Doctor;
use App\Models\Notification;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Support\Facades\Log; // اضافه کردن Log
use Illuminate\Support\Facades\Validator;
use Livewire\Component;
use Morilog\Jalali\Jalalian;

class NotificationCreate extends Component
{
    public $title, $message, $type = 'info', $target_group, $single_phone, $selected_recipients = [];
    public $is_active              = true, $start_at, $end_at;
    public $target_mode            = 'group';

    public function store()
    {
        $data = [
            'title'               => $this->title,
            'message'             => $this->message,
            'type'                => $this->type,
            'target_mode'         => $this->target_mode,
            'target_group'        => $this->target_mode === 'group' ? $this->target_group : null,
            'single_phone'        => $this->target_mode === 'single' ? $this->single_phone : null,
            'selected_recipients' => $this->target_mode === 'multiple' ? $this->selected_recipients : null,
            'start_at'            => $this->start_at,
            'end_at'              => $this->end_at,
            'is_active'           => $this->is_active,
        ];

        $rules = [
            'title'               => 'required|string|max:255',
            'message'             => 'required|string',
            'type'                => 'required|in:info,success,warning,error',
            'target_mode'         => 'required|in:group,single,multiple',
            'target_group'        => 'required_if:target_mode,group|in:all,doctors,secretaries,patients|nullable',
            'single_phone'        => 'required_if:target_mode,single|regex:/^09[0-9]{9}$/|nullable',
            'selected_recipients' => 'required_if:target_mode,multiple|array|min:1|nullable',
            'start_at'            => 'nullable|string|max:19',
            'end_at'              => 'nullable|string|max:19',
            'is_active'           => 'required|boolean',
        ];

        $messages = [
            'title.required'                  => 'لطفاً عنوان را وارد کنید.',
            'title.string'                    => 'عنوان باید متن باشد.',
            'title.max'                       => 'عنوان نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'message.required'                => 'لطفاً پیام را وارد کنید.',
            'message.string'                  => 'پیام باید متن باشد.',
            'type.required'                   => 'لطفاً نوع اعلان را انتخاب کنید.',
            'type.in'                         => 'نوع اعلان باید یکی از گزینه‌های موجود باشد.',
            'target_mode.required'            => 'لطفاً حالت هدف را انتخاب کنید.',
            'target_mode.in'                  => 'حالت هدف باید یکی از گزینه‌های موجود باشد.',
            'target_group.required_if'        => 'لطفاً گروه هدف را انتخاب کنید.',
            'target_group.in'                 => 'گروه هدف باید یکی از گزینه‌های موجود باشد.',
            'single_phone.required_if'        => 'لطفاً شماره تلفن را وارد کنید.',
            'single_phone.regex'              => 'شماره تلفن باید با ۰۹ شروع شود و ۱۱ رقم باشد.',
            'selected_recipients.required_if' => 'لطفاً حداقل یک گیرنده انتخاب کنید.',
            'selected_recipients.array'       => 'گیرندگان باید به‌صورت لیست باشند.',
            'selected_recipients.min'         => 'حداقل یک گیرنده باید انتخاب شود.',
            'start_at.string'                 => 'زمان شروع باید متن باشد.',
            'start_at.max'                    => 'زمان شروع باید حداکثر ۱۹ کاراکتر باشد (مثال: ۱۴۰۳/۱۲/۱۳ ۱۴:۳۰:۰۰).',
            'end_at.string'                   => 'زمان پایان باید متن باشد.',
            'end_at.max'                      => 'زمان پایان باید حداکثر ۱۹ کاراکتر باشد (مثال: ۱۴۰۳/۱۲/۱۳ ۱۴:۳۰:۰۰).',
            'is_active.required'              => 'لطفاً وضعیت را مشخص کنید.',
            'is_active.boolean'               => 'وضعیت باید فعال یا غیرفعال باشد.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        $startAtMiladi = null;
        if ($this->start_at) {
            try {
                $startAtMiladi = Jalalian::fromFormat('Y/m/d H:i:s', $this->start_at)->toCarbon()->toDateTimeString();
            } catch (\Exception $e1) {
                try {
                    $startAtMiladi = Jalalian::fromFormat('Y/m/d H:i', $this->start_at)->toCarbon()->toDateTimeString();
                } catch (\Exception $e2) {
                    $this->dispatch('show-alert', type: 'error', message: 'زمان شروع نامعتبر است. لطفاً به فرمت ۱۴۰۳/۱۲/۱۳ ۱۴:۳۰ یا ۱۴۰۳/۱۲/۱۳ ۱۴:۳۰:۰۰ وارد کنید.');
                    return;
                }
            }
        }

        $endAtMiladi = null;
        if ($this->end_at) {
            try {
                $endAtMiladi = Jalalian::fromFormat('Y/m/d H:i:s', $this->end_at)->toCarbon()->toDateTimeString();
            } catch (\Exception $e1) {
                try {
                    $endAtMiladi = Jalalian::fromFormat('Y/m/d H:i', $this->end_at)->toCarbon()->toDateTimeString();
                } catch (\Exception $e2) {
                    $this->dispatch('show-alert', type: 'error', message: 'زمان پایان نامعتبر است. لطفاً به فرمت ۱۴۰۳/۱۲/۱۳ ۱۴:۳۰ یا ۱۴۰۳/۱۲/۱۳ ۱۴:۳۰:۰۰ وارد کنید.');
                    return;
                }
            }
            if ($startAtMiladi && $endAtMiladi < $startAtMiladi) {
                $this->dispatch('show-alert', type: 'error', message: 'زمان پایان باید بعد از زمان شروع باشد.');
                return;
            }
        }

        $notificationData = [
            'title'        => $this->title,
            'message'      => $this->message,
            'type'         => $this->type,
            'target_group' => $this->target_mode === 'group' ? $this->target_group : null,
            'is_active'    => $this->is_active,
            'start_at'     => $startAtMiladi,
            'end_at'       => $endAtMiladi,
            'created_by'   => auth()->id(),
        ];

        $notification = Notification::create($notificationData);

        $recipientNumbers = []; // آرایه شماره‌های گیرنده

        Log::info('شروع جمع‌آوری گیرنده‌ها', [
            'target_mode' => $this->target_mode,
            'is_active'   => $this->is_active,
        ]);

        if ($this->target_mode === 'single') {
            $notification->recipients()->create([
                'recipient_type' => 'phone',
                'recipient_id'   => null,
                'mobile'   => $this->single_phone,
            ]);
            $recipientNumbers = [$this->single_phone];
        } elseif ($this->target_mode === 'multiple') {
            foreach ($this->selected_recipients as $recipient) {
                [$type, $id] = explode(':', $recipient);
                $notification->recipients()->create([
                    'recipient_type' => $type,
                    'recipient_id'   => $id,
                ]);
                $model = $type::find($id);
                if ($model && $model->mobile) {
                    $recipientNumbers[] = $model->mobile;
                }
            }
        } elseif ($this->target_mode === 'group') {
            $recipients = match ($this->target_group) {
                'all' => collect()->merge(User::all())->merge(Doctor::all())->merge(Secretary::all()),
                'doctors'     => Doctor::all(),
                'secretaries' => Secretary::all(),
                'patients'    => User::all(),
            };
            foreach ($recipients as $recipient) {
                $notification->recipients()->create([
                    'recipient_type' => $recipient->getMorphClass(),
                    'recipient_id'   => $recipient->id,
                ]);
                if ($recipient->mobile) {
                    $recipientNumbers[] = $recipient->mobile;
                }
            }
        }

        Log::info('گیرنده‌ها جمع‌آوری شدند', [
            'recipient_numbers' => $recipientNumbers,
            'is_active'         => $this->is_active,
        ]);

        // اضافه کردن ارسال پیامک به صف
        if (! empty($recipientNumbers) && $this->is_active) {
            $chunks = array_chunk($recipientNumbers, 10); // تکه‌تکه کردن به گروه‌های 10 تایی
            $delay  = 0;
            foreach ($chunks as $chunk) {
                Log::info('ارسال Job به صف', [
                    'chunk' => $chunk,
                    'delay' => $delay,
                ]);
                SendNotificationSms::dispatch($this->message, $chunk)
                    ->delay(now()->addSeconds($delay)); // تاخیر بین هر گروه
                $delay += 5;                        // 5 ثانیه تاخیر بین هر گروه
            }
            $this->dispatch('show-alert', type: 'success', message: 'اعلان ایجاد و ارسال پیامک‌ها در صف قرار گرفت!');
        } else {
            Log::warning('ارسال به صف انجام نشد', [
                'recipient_numbers_empty' => empty($recipientNumbers),
                'is_active'               => $this->is_active,
            ]);
            $this->dispatch('show-alert', type: 'success', message: 'اعلان با موفقیت ایجاد شد!');
        }

        return redirect()->route('admin.panel.tools.notifications.index');
    }

    public function render()
    {
        $allRecipients = collect()
            ->merge(User::all()->map(fn($u) => ['id' => "App\\Models\\User:{$u->id}", 'text' => $u->first_name . ' ' . $u->last_name . ' (بیمار)']))
            ->merge(Doctor::all()->map(fn($d) => ['id' => "App\\Models\\Doctor:{$d->id}", 'text' => $d->first_name . ' ' . $d->last_name . ' (پزشک)']))
            ->merge(Secretary::all()->map(fn($s) => ['id' => "App\\Models\\Secretary:{$s->id}", 'text' => $s->first_name . ' ' . $s->last_name . ' (منشی)']));

        return view('livewire.admin.panel.tools.notification-create', [
            'allRecipients' => $allRecipients,
        ])->layout('layouts.admin');
    }
}
