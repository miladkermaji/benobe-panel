<?php

namespace App\Livewire\Admin\Panel\Subusers;

use Livewire\Component;
use App\Models\SubUser;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SubUserCreate extends Component
{
    public $owner_type = '';
    public $owner_id = '';
    public $user_id;
    public $status = 'active';
    public $owners = [];
    public $ownerTypes = [
        'App\\Models\\Doctor' => 'پزشک',
        'App\\Models\\Secretary' => 'منشی',
        'App\\Models\\Admin\\Manager' => 'مدیر',
        'App\\Models\\User' => 'کاربر عادی',
    ];

    public function updatedOwnerType($value)
    {
        // لود لیست ownerها بر اساس owner_type
        $model = $value;
        if (class_exists($model)) {
            $this->owners = $model::all();
        } else {
            $this->owners = [];
        }
        $this->owner_id = '';
    }

    public function mount()
    {
        $this->owner_type = 'App\\Models\\Doctor';
        $this->owners = \App\Models\Doctor::all();
    }

    public function store()
    {
        try {
            $validated = $this->validate([
                'owner_type' => 'required|string',
                'owner_id' => 'required|integer',
                'user_id' => [
                    'required',
                    'exists:users,id',
                    function ($attribute, $value, $fail) {
                        $exists = \App\Models\SubUser::where('owner_id', $this->owner_id)
                            ->where('owner_type', $this->owner_type)
                            ->where('subuserable_id', $value)
                            ->where('subuserable_type', \App\Models\User::class)
                            ->exists();
                        if ($exists) {
                            $fail('این کاربر قبلاً به‌عنوان زیرمجموعه این مالک ثبت شده است.');
                        }
                    }
                ],
                'status' => 'required|in:active,inactive',
            ], [
                'owner_type.required' => 'لطفاً نوع مالک را انتخاب کنید.',
                'owner_id.required' => 'لطفاً مالک را انتخاب کنید.',
                'user_id.required' => 'لطفاً کاربر را انتخاب کنید.',
                'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
                'status.required' => 'لطفاً وضعیت را مشخص کنید.',
                'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
            ]);

            \App\Models\SubUser::create([
                'owner_id' => $this->owner_id,
                'owner_type' => $this->owner_type,
                'subuserable_id' => $this->user_id,
                'subuserable_type' => \App\Models\User::class,
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
            'owner_type' => 'required|string',
            'owner_id' => 'required|integer',
            'user_id' => [
                'required',
                'exists:users,id',
                function (
                    $attribute,
                    $value,
                    $fail
                ) {
                    $exists = \App\Models\SubUser::where('owner_id', $this->owner_id)
                        ->where('owner_type', $this->owner_type)
                        ->where('subuserable_id', $value)
                        ->where('subuserable_type', \App\Models\User::class)
                        ->exists();
                    if ($exists) {
                        $fail('این کاربر قبلاً به‌عنوان زیرمجموعه این مالک ثبت شده است.');
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
