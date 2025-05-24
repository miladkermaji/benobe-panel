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
    public $users;
    public $doctors;
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
        $this->users = User::select('id', 'first_name', 'last_name', 'mobile')->get();
        $this->doctors = Doctor::select('id', 'first_name', 'last_name', 'mobile')->get();
        $this->clinics = Clinic::select('id', 'name')->get();
        $this->dispatch('select2:refresh');
    }

    public function updatedType($value)
    {
        $this->user_id = null;
        $this->dispatch('select2:refresh');
    }

    public function update()
    {
        $this->validate([
            'type' => 'required|in:user,doctor',
            'user_id' => 'required_if:type,user|exists:users,id|nullable',
            'user_id' => 'required_if:type,doctor|exists:doctors,id|nullable',
            'blocked_at' => 'required',
            'unblocked_at' => 'nullable|after:blocked_at',
            'reason' => 'nullable|string|max:255',
            'clinic_id' => 'nullable|exists:clinics,id',
            'status' => 'required|boolean',
        ], [
            'type.required' => 'لطفاً نوع کاربر را انتخاب کنید.',
            'type.in' => 'نوع کاربر نامعتبر است.',
            'user_id.required_if' => 'لطفاً کاربر یا پزشک را انتخاب کنید.',
            'user_id.exists' => 'کاربر یا پزشک انتخاب‌شده معتبر نیست.',
            'blocked_at.required' => 'لطفاً تاریخ شروع مسدودیت را وارد کنید.',
            'unblocked_at.after' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
            'reason.max' => 'دلیل مسدودیت نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        ]);

        // تبدیل تاریخ جلالی به میلادی
        $blockedAtMiladi = Jalalian::fromFormat('Y/m/d', $this->blocked_at)->toCarbon();
        $unblockedAtMiladi = $this->unblocked_at ? Jalalian::fromFormat('Y/m/d', $this->unblocked_at)->toCarbon() : null;

        $this->userBlocking->update([
            'user_id' => $this->type === 'user' ? $this->user_id : null,
            'doctor_id' => $this->type === 'doctor' ? $this->user_id : null,
            'blocked_at' => $blockedAtMiladi,
            'unblocked_at' => $unblockedAtMiladi,
            'reason' => $this->reason,
            'clinic_id' => $this->clinic_id,
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
