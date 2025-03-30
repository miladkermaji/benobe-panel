<?php

namespace App\Livewire\Admin\Panel\UserBlockings;

use Livewire\Component;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\UserBlocking;
use App\Jobs\SendSmsNotificationJob;
use Morilog\Jalali\Jalalian;

class UserBlockingEdit extends Component
{
    public $userBlocking;
    public $user_id;
    public $doctor_id;
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
        $this->user_id = $this->userBlocking->user_id;
        $this->doctor_id = $this->userBlocking->doctor_id;

        // تاریخ میلادی از دیتابیس به جلالی برای نمایش
        $this->blocked_at = Jalalian::fromCarbon($this->userBlocking->blocked_at)->format('Y/m/d');
        $this->unblocked_at = $this->userBlocking->unblocked_at
            ? Jalalian::fromCarbon($this->userBlocking->unblocked_at)->format('Y/m/d')
            : null;

        $this->reason = $this->userBlocking->reason;
        $this->clinic_id = $this->userBlocking->clinic_id;
        $this->status = $this->userBlocking->status;
        $this->users = User::all();
        $this->doctors = Doctor::all();
        $this->clinics = Clinic::all();
    }

    public function update()
    {
        $this->validate([
            'user_id' => 'nullable|exists:users,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'blocked_at' => 'required',
            'unblocked_at' => 'nullable|after:blocked_at',
            'reason' => 'nullable|string|max:255',
            'clinic_id' => 'nullable|exists:clinics,id',
            'status' => 'required|boolean',
        ]);

        if (!$this->user_id && !$this->doctor_id) {
            $this->addError('user_id', 'لطفاً حداقل یک کاربر یا دکتر را انتخاب کنید.');
            $this->addError('doctor_id', 'لطفاً حداقل یک کاربر یا دکتر را انتخاب کنید.');
            return;
        }

        // تبدیل تاریخ جلالی به میلادی برای ذخیره
        $blockedAtMiladi = Jalalian::fromFormat('Y/m/d', $this->blocked_at)->toCarbon();
        $unblockedAtMiladi = $this->unblocked_at ? Jalalian::fromFormat('Y/m/d', $this->unblocked_at)->toCarbon() : null;

        $this->userBlocking->update([
            'user_id' => $this->user_id,
            'doctor_id' => $this->doctor_id,
            'blocked_at' => $blockedAtMiladi,
            'unblocked_at' => $unblockedAtMiladi,
            'reason' => $this->reason,
            'clinic_id' => $this->clinic_id,
            'status' => $this->status,
        ]);

        if ($this->status && !$this->userBlocking->is_notified) {
            if ($this->user_id) {
                $user = User::find($this->user_id);
                $message = "کاربر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
            }
            if ($this->doctor_id) {
                $doctor = Doctor::find($this->doctor_id);
                $message = "دکتر گرامی، شما توسط مدیر سیستم مسدود شده‌اید. جهت اطلاعات بیشتر تماس بگیرید.";
                SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
            }
            $this->userBlocking->update(['is_notified' => true]);
        } elseif (!$this->status) {
            if ($this->user_id) {
                $user = User::find($this->user_id);
                $message = "کاربر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                SendSmsNotificationJob::dispatch($message, [$user->mobile])->delay(now()->addSeconds(5));
            }
            if ($this->doctor_id) {
                $doctor = Doctor::find($this->doctor_id);
                $message = "دکتر گرامی، مسدودیت شما توسط مدیر سیستم رفع شد.";
                SendSmsNotificationJob::dispatch($message, [$doctor->mobile])->delay(now()->addSeconds(5));
            }
        }

        $this->dispatch('show-alert', type: 'success', message: 'اطلاعات مسدودیت با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.userblockings.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.userblockings.userblocking-edit');
    }
}
