<?php

namespace App\Livewire\Admin\Panel\UserBlockings;

use Livewire\Component;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\UserBlocking;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class UserBlockingEdit extends Component
{
    public $userBlocking;
    public $type;
    public $user_id;
    public $blocked_at;
    public $unblocked_at;
    public $reason;
    public $clinic_id;
    public $status;
    public $clinics;

    public function mount($id)
    {
        $this->userBlocking = UserBlocking::findOrFail($id);
        $this->type = $this->userBlocking->user_id ? 'user' : 'doctor';
        $this->user_id = $this->userBlocking->user_id ?? $this->userBlocking->doctor_id;
        $this->blocked_at = Jalalian::fromCarbon($this->userBlocking->blocked_at)->format('Y/m/d');
        $this->unblocked_at = $this->userBlocking->unblocked_at
            ? Jalalian::fromCarbon($this->userBlocking->unblocked_at)->format('Y/m/d')
            : null;
        $this->reason = $this->userBlocking->reason;
        $this->clinic_id = $this->userBlocking->clinic_id;
        $this->status = $this->userBlocking->status;
        $this->clinics = Clinic::select('id', 'name')->get();
    }

    public function updatedType($value)
    {
        $this->user_id = null;
    }

    public function update()
    {
        $this->validate([
            'type' => 'required|in:user,doctor',
            'user_id' => 'required|exists:' . ($this->type == 'user' ? 'users' : 'doctors') . ',id',
            'blocked_at' => 'required|string|max:10',
            'unblocked_at' => 'nullable|string|max:10',
            'reason' => 'required|string|max:255',
            'status' => 'required|boolean',
        ], [
            'type.required' => 'لطفاً نوع کاربر را انتخاب کنید.',
            'type.in' => 'نوع کاربر باید "کاربر" یا "پزشک" باشد.',
            'user_id.required' => 'لطفاً کاربر را انتخاب کنید.',
            'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
            'blocked_at.required' => 'لطفاً تاریخ شروع را وارد کنید.',
            'blocked_at.string' => 'تاریخ شروع باید متن باشد.',
            'blocked_at.max' => 'تاریخ شروع نباید بیشتر از ۱۰ کاراکتر باشد.',
            'unblocked_at.string' => 'تاریخ پایان باید متن باشد.',
            'unblocked_at.max' => 'تاریخ پایان نباید بیشتر از ۱۰ کاراکتر باشد.',
            'reason.required' => 'لطفاً دلیل را وارد کنید.',
            'reason.string' => 'دلیل باید متن باشد.',
            'reason.max' => 'دلیل نباید بیشتر از ۲۵۵ کاراکتر باشد.',
            'status.required' => 'لطفاً وضعیت را مشخص کنید.',
            'status.boolean' => 'وضعیت باید فعال یا غیرفعال باشد.',
        ]);

        // Check for existing active blocking
        $existingBlocking = UserBlocking::where(function ($query) {
            if ($this->type == 'user') {
                $query->where('user_id', $this->user_id);
            } else {
                $query->where('doctor_id', $this->user_id);
            }
        })->where('status', true)
          ->where('id', '!=', $this->userBlocking->id)
          ->first();

        if ($existingBlocking) {
            $this->dispatch('show-alert', type: 'error', message: 'این کاربر قبلاً مسدود شده است.');
            return;
        }

        $blockedAtMiladi = null;
        if ($this->blocked_at) {
            try {
                $blockedAtMiladi = Jalalian::fromFormat('Y/m/d', $this->blocked_at)->toCarbon();
            } catch (\Exception $e) {
                $this->dispatch('show-alert', type: 'error', message: 'تاریخ شروع نامعتبر است. لطفاً به فرمت ۱۴۰۳/۱۲/۱۳ وارد کنید.');
                return;
            }
        }

        $unblockedAtMiladi = null;
        if ($this->unblocked_at) {
            try {
                $unblockedAtMiladi = Jalalian::fromFormat('Y/m/d', $this->unblocked_at)->toCarbon();
            } catch (\Exception $e) {
                $this->dispatch('show-alert', type: 'error', message: 'تاریخ پایان نامعتبر است. لطفاً به فرمت ۱۴۰۳/۱۲/۱۳ وارد کنید.');
                return;
            }
        }

        if ($unblockedAtMiladi && $blockedAtMiladi && $unblockedAtMiladi->lt($blockedAtMiladi)) {
            $this->dispatch('show-alert', type: 'error', message: 'تاریخ پایان نمی‌تواند قبل از تاریخ شروع باشد.');
            return;
        }

        $this->userBlocking->update([
            'type' => $this->type,
            'user_id' => $this->type == 'user' ? $this->user_id : null,
            'doctor_id' => $this->type == 'doctor' ? $this->user_id : null,
            'blocked_at' => $blockedAtMiladi,
            'unblocked_at' => $unblockedAtMiladi,
            'reason' => $this->reason,
            'status' => $this->status,
        ]);

        if ($this->status && !$this->userBlocking->is_notified) {
            if ($this->type === 'user') {
                $user = User::find($this->user_id);
                $message = "کاربر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
            } else {
                $doctor = Doctor::find($this->user_id);
                $message = "دکتر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
            }
            $this->userBlocking->update(['is_notified' => true]);
        } elseif (!$this->status) {
            if ($this->type === 'user') {
                $user = User::find($this->user_id);
                $message = "کاربر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
            } else {
                $doctor = Doctor::find($this->user_id);
                $message = "دکتر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
            }
        }

        $this->dispatch('show-alert', type: 'success', message: 'اطلاعات مسدودیت با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.user-blockings.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-blockings.user-blocking-edit');
    }
}
