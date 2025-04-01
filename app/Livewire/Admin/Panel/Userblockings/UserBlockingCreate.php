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

class UserBlockingCreate extends Component
{
    public $user_id;
    public $doctor_id;
    public $blocked_at;
    public $unblocked_at;
    public $reason;
    public $clinic_id;
    public $status = true;
    public $users;
    public $doctors;
    public $clinics;

    public function mount()
    {
        $this->users = User::all();
        $this->doctors = Doctor::all();
        $this->clinics = Clinic::all();
    }

    public function store()
    {
        $managerId = Auth::guard('manager')->user()->id;

        $this->validate([
            'user_id' => 'nullable|exists:users,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'blocked_at' => 'required',
            'unblocked_at' => 'nullable|after:blocked_at',
            'reason' => 'nullable|string|max:255',
            'clinic_id' => 'nullable|exists:clinics,id',
            'status' => 'required|boolean',
        ], [
            'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
            'doctor_id.exists' => 'دکتر انتخاب‌شده معتبر نیست.',
            'blocked_at.required' => 'لطفاً تاریخ شروع مسدودیت را وارد کنید.',
            'unblocked_at.after' => 'تاریخ پایان باید بعد از تاریخ شروع باشد.',
            'reason.max' => 'دلیل مسدودیت نمی‌تواند بیشتر از 255 کاراکتر باشد.',
        ]);

        if (!$this->user_id && !$this->doctor_id) {
            $this->addError('user_id', 'لطفاً حداقل یک کاربر یا دکتر را انتخاب کنید.');
            $this->addError('doctor_id', 'لطفاً حداقل یک کاربر یا دکتر را انتخاب کنید.');
            return;
        }

        // تبدیل تاریخ جلالی به میلادی
        $blockedAtMiladi = Jalalian::fromFormat('Y/m/d', $this->blocked_at)->toCarbon();
        $unblockedAtMiladi = $this->unblocked_at ? Jalalian::fromFormat('Y/m/d', $this->unblocked_at)->toCarbon() : null;

        $blocking = UserBlocking::create([
            'user_id' => $this->user_id,
            'doctor_id' => $this->doctor_id,
            'manager_id' => $managerId,
            'clinic_id' => $this->clinic_id,
            'blocked_at' => $blockedAtMiladi,
            'unblocked_at' => $unblockedAtMiladi,
            'reason' => $this->reason,
            'status' => $this->status,
        ]);

        if ($this->status) {
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
        }

        $this->dispatch('show-alert', type: 'success', message: 'مسدودیت با موفقیت ثبت شد!');
        return redirect()->route('admin.panel.user-blockings.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-blockings.user-blocking-create');
    }
}
