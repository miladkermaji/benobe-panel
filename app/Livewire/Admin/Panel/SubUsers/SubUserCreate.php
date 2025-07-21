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
                'user_id' => [
                    'required',
                    'exists:users,id',
                    function ($attribute, $value, $fail) {
                        $exists = \App\Models\SubUser::where('owner_id', $this->doctor_id)
                            ->where('owner_type', \App\Models\Doctor::class)
                            ->where('subuserable_id', $value)
                            ->where('subuserable_type', \App\Models\User::class)
                            ->exists();
                        if ($exists) {
                            $fail('این کاربر قبلاً به‌عنوان زیرمجموعه این پزشک ثبت شده است.');
                        }
                    }
                ],
                'status' => 'required|in:active,inactive',
            ], [
                'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
                'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
                'user_id.required' => 'لطفاً کاربر را انتخاب کنید.',
                'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
                'status.required' => 'لطفاً وضعیت را مشخص کنید.',
                'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
            ]);

            \App\Models\SubUser::create([
                'owner_id' => $this->doctor_id,
                'owner_type' => \App\Models\Doctor::class,
                'subuserable_id' => $this->user_id,
                'subuserable_type' => User::class,
                'status' => $this->status,
            ]);

            session()->flash('success', 'کاربر زیرمجموعه با موفقیت ثبت شد!');
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
            'user_id' => [
                'required',
                'exists:users,id',
                function (
                    $attribute,
                    $value,
                    $fail
                ) {
                    $exists = \App\Models\SubUser::where('owner_id', $this->doctor_id)
                        ->where('owner_type', \App\Models\Doctor::class)
                        ->where('subuserable_id', $value)
                        ->where('subuserable_type', \App\Models\User::class)
                        ->exists();
                    if ($exists) {
                        $fail('این کاربر قبلاً به‌عنوان زیرمجموعه این پزشک ثبت شده است.');
                    }
                }
            ],
            'status' => 'required|in:active,inactive',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.panel.sub-users.sub-user-create');
    }
}
