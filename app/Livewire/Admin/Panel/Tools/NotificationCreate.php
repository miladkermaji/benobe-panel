<?php

namespace App\Livewire\Admin\Panel\Tools;

//new changes added by me2222222222
use App\Models\User;
use App\Models\Doctor;
use Livewire\Component;
use App\Models\Secretary;
use App\Models\Notification;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Jobs\Admin\Panel\Tools\SendNotificationSms;
use Illuminate\Support\Facades\Log; // اضافه کردن Log

class NotificationCreate extends Component
{
    public $title;
    public $message;
    public $type = 'info';
    public $target_group;
    public $single_phone;
    public $selected_recipients = [];
    public $is_active              = true;
    public $start_at;
    public $end_at;
    public $target_mode            = 'group';

    // --- اضافه کردن متغیرهای کش برای کاربران، پزشکان و منشی‌ها ---
    protected $users;
    protected $doctors;
    protected $secretaries;

    public function mount()
    {
        $this->users = User::select('id', 'first_name', 'last_name', 'mobile')->get();
        $this->doctors = Doctor::select('id', 'first_name', 'last_name', 'mobile')->get();
        $this->secretaries = Secretary::select('id', 'first_name', 'last_name', 'mobile')->get();
    }

    public function store()
    {
        // اطمینان از مقداردهی متغیرهای کش قبل از استفاده
        if (!$this->users) {
            $this->users = User::select('id', 'first_name', 'last_name', 'mobile')->get();
        }
        if (!$this->doctors) {
            $this->doctors = Doctor::select('id', 'first_name', 'last_name', 'mobile')->get();
        }
        if (!$this->secretaries) {
            $this->secretaries = Secretary::select('id', 'first_name', 'last_name', 'mobile')->get();
        }
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
            'created_by'   => Auth::guard('manager')->user()->id,
        ];

        $notification = Notification::create($notificationData);
        $recipientNumbers = [];
        $recipientsToInsert = [];
        Log::info('شروع جمع‌آوری گیرنده‌ها', [
            'target_mode' => $this->target_mode,
            'is_active'   => $this->is_active,
        ]);
        if ($this->target_mode === 'single') {
            $recipientsToInsert[] = [
                'notification_id' => $notification->id,
                'recipient_type' => 'phone',
                'recipient_id'   => null,
                'phone_number'   => $this->single_phone,
            ];
            $recipientNumbers = [$this->single_phone];
        } elseif ($this->target_mode === 'multiple') {
            foreach ($this->selected_recipients as $recipient) {
                [$type, $id] = explode(':', $recipient);
                $model = null;
                if ($type === 'App\\Models\\User') {
                    $model = $this->users->firstWhere('id', $id);
                } elseif ($type === 'App\\Models\\Doctor') {
                    $model = $this->doctors->firstWhere('id', $id);
                } elseif ($type === 'App\\Models\\Secretary') {
                    $model = $this->secretaries->firstWhere('id', $id);
                }
                $recipientsToInsert[] = [
                    'notification_id' => $notification->id,
                    'recipient_type' => $type,
                    'recipient_id'   => $id,
                    'phone_number'   => $model->mobile ?? null,
                ];
                if ($model && $model->mobile) {
                    $recipientNumbers[] = $model->mobile;
                }
            }
        } elseif ($this->target_mode === 'group') {
            $recipients = match ($this->target_group) {
                'all' => $this->users->concat($this->doctors)->concat($this->secretaries),
                'doctors' => $this->doctors,
                'secretaries' => $this->secretaries,
                'patients' => $this->users,
            };
            foreach ($recipients as $recipient) {
                $recipientsToInsert[] = [
                    'notification_id' => $notification->id,
                    'recipient_type' => get_class($recipient),
                    'recipient_id'   => $recipient->id,
                    'phone_number'   => $recipient->mobile ?? null,
                ];
                if ($recipient->mobile) {
                    $recipientNumbers[] = $recipient->mobile;
                }
            }
        }
        if (count($recipientsToInsert)) {
            $notification->recipients()->insert($recipientsToInsert);
        }
        Log::info('گیرنده‌ها جمع‌آوری شدند', [
            'recipient_numbers' => $recipientNumbers,
            'is_active'         => $this->is_active,
        ]);

        // اضافه کردن ارسال پیامک به صف
        if (! empty($recipientNumbers) && $this->is_active) {
            $chunks      = array_chunk($recipientNumbers, 10);
            $delay       = 0;
            $fullMessage = $this->title . "\n" . $this->message; // ترکیب عنوان و متن با خط جدید
            foreach ($chunks as $chunk) {
                Log::info('ارسال Job به صف', [
                    'chunk'        => $chunk,
                    'delay'        => $delay,
                    'sendDateTime' => $this->start_at,
                ]);
                SendNotificationSms::dispatch(
                    $fullMessage,
                    $chunk,
                    $this->start_at
                )->delay(now()->addSeconds($delay));
                $delay += 5;
            }
            $this->dispatch('show-alert', type: 'success', message: 'اعلان ایجاد و ارسال پیامک‌ها در صف قرار گرفت!');
        }

        return redirect()->route('admin.panel.tools.notifications.index');
    }

    public function render()
    {
        if (empty($this->users)) {
            $this->users = User::select('id', 'first_name', 'last_name', 'mobile')->get();
        }
        if (empty($this->doctors)) {
            $this->doctors = Doctor::select('id', 'first_name', 'last_name', 'mobile')->get();
        }
        if (empty($this->secretaries)) {
            $this->secretaries = Secretary::select('id', 'first_name', 'last_name', 'mobile')->get();
        }
        $allRecipients = collect()
            ->merge($this->users->map(fn ($u) => ['id' => "App\\Models\\User:{$u->id}", 'text' => $u->first_name . ' ' . $u->last_name . ' (بیمار)']))
            ->merge($this->doctors->map(fn ($d) => ['id' => "App\\Models\\Doctor:{$d->id}", 'text' => $d->first_name . ' ' . $d->last_name . ' (پزشک)']))
            ->merge($this->secretaries->map(fn ($s) => ['id' => "App\\Models\\Secretary:{$s->id}", 'text' => $s->first_name . ' ' . $s->last_name . ' (منشی)']));
        return view('livewire.admin.panel.tools.notification-create', [
            'allRecipients' => $allRecipients,
        ]);
    }
}
