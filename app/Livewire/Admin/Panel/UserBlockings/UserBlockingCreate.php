<?php

namespace App\Livewire\Admin\Panel\UserBlockings;

use Livewire\Component;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\UserBlocking;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class UserBlockingCreate extends Component
{
    public $type = '';
    public $user_id;
    public $blocked_at;
    public $unblocked_at;
    public $reason;
    public $clinic_id;
    public $status = true;
    public $users = [];
    public $doctors = [];
    public $clinics = [];

    public function mount()
    {
        $this->users = User::select('id', 'first_name', 'last_name', 'mobile')->get();
        $this->doctors = Doctor::select('id', 'first_name', 'last_name', 'mobile')->get();
        $this->clinics = Clinic::select('id', 'name')->get();
    }

    public function updatedType($value)
    {
        $this->user_id = null;
        $this->dispatch('select2:refresh');
    }

    public function getUsersProperty()
    {
        return $this->type === 'user' ? $this->users : $this->doctors;
    }

    public function save()
    {
        $managerId = Auth::guard('manager')->user()->id;

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

        UserBlocking::create([
            'user_id' => $this->type === 'user' ? $this->user_id : null,
            'doctor_id' => $this->type === 'doctor' ? $this->user_id : null,
            'manager_id' => $managerId,
            'clinic_id' => $this->clinic_id,
            'blocked_at' => $blockedAtMiladi,
            'unblocked_at' => $unblockedAtMiladi,
            'reason' => $this->reason,
            'status' => $this->status,
        ]);

        $this->dispatch('show-alert', type: 'success', message: 'مسدودیت با موفقیت ثبت شد!');
        return redirect()->route('admin.panel.user-blockings.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.user-blockings.user-blocking-create');
    }
}
