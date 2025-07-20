<?php

namespace App\Livewire\Admin\Panel\Subusers;

use Livewire\Component;
use App\Models\SubUser;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SubUserCreate extends Component
{
    public $doctor_id;
    public $user_id;
    public $status = 'active';
    public $doctors;

    public function mount()
    {
        $this->doctors = Doctor::all();
        // Remove users loading - will use AJAX instead
    }

    public function store()
    {
        try {
            $validated = $this->validate([
                'doctor_id' => 'required|exists:doctors,id',
                'user_id' => 'required|exists:users,id|unique:sub_users,user_id,NULL,id,doctor_id,' . $this->doctor_id,
                'status' => 'required|in:active,inactive',
            ], [
                'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
                'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
                'user_id.required' => 'لطفاً کاربر را انتخاب کنید.',
                'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
                'user_id.unique' => 'این کاربر قبلاً به‌عنوان زیرمجموعه این پزشک ثبت شده است.',
                'status.required' => 'لطفاً وضعیت را مشخص کنید.',
                'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
            ]);

            SubUser::create($validated);

            $this->dispatch(
                'show-alert',
                type: 'success',
                message: 'کاربر زیرمجموعه با موفقیت ثبت شد!'
            );

            return redirect()->route('admin.panel.sub-users.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch(
                'show-alert',
                type: 'error',
                message: $e->validator->errors()->first()
            );
        }
    }

    // متد برای گرفتن خطاها
    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'doctor_id' => 'required|exists:doctors,id',
            'user_id' => 'required|exists:users,id|unique:sub_users,user_id,NULL,id,doctor_id,' . $this->doctor_id,
            'status' => 'required|in:active,inactive',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.panel.sub-users.sub-user-create');
    }
}
