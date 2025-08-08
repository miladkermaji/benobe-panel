<?php

namespace App\Livewire\Admin\Panel\UserBlockings;

use Livewire\Component;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\UserBlocking;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Validator;
use App\Models\MedicalCenter;

class UserBlockingCreate extends Component
{
    public $type = '';
    public $user_id;
    public $blocked_at;
    public $unblocked_at;
    public $reason;
    public $medical_center_id;
    public $status = true;
    public $clinics = [];
    public $doctors = [];
    public $users = [];

    public function mount()
    {
        $this->clinics = MedicalCenter::select('id', 'name')->get();
        $this->doctors = Doctor::select('id', 'name')->get();
        $this->users = User::select('id', 'name')->get();
    }

    public function updatedType($value)
    {
        $this->user_id = null;
    }

    public function getUsersProperty()
    {
        return $this->type === 'user' ? $this->users : $this->doctors;
    }

    public function save()
    {
        $validator = Validator::make([
            'type' => $this->type,
            'user_id' => $this->user_id,
            'blocked_at' => $this->blocked_at,
            'unblocked_at' => $this->unblocked_at,
            'reason' => $this->reason,
            'status' => $this->status,
        ], [
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

        if ($validator->fails()) {
            $this->dispatch('show-alert', type: 'error', message: $validator->errors()->first());
            return;
        }

        // Check for existing active blocking
        $existingBlocking = UserBlocking::where(function ($query) {
            if ($this->type == 'user') {
                $query->where('user_id', $this->user_id);
            } else {
                $query->where('doctor_id', $this->user_id);
            }
        })->where('status', true)->first();

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

        $userBlocking = UserBlocking::create([
            'type' => $this->type,
            'user_id' => $this->type == 'user' ? $this->user_id : null,
            'doctor_id' => $this->type == 'doctor' ? $this->user_id : null,
            'blocked_at' => $blockedAtMiladi,
            'unblocked_at' => $unblockedAtMiladi,
            'reason' => $this->reason,
            'status' => $this->status,
            'manager_id' => \Illuminate\Support\Facades\Auth::guard('manager')->user()->id,
        ]);

        if ($userBlocking) {
            $this->dispatch('show-alert', type: 'success', message: 'کاربر با موفقیت مسدود شد!');
            return redirect()->route('admin.panel.user-blockings.index');
        }

        $this->dispatch('show-alert', type: 'error', message: 'خطا در مسدود کردن کاربر!');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-blockings.user-blocking-create');
    }
}
