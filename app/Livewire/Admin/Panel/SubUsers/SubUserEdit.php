<?php

namespace App\Livewire\Admin\Panel\Subusers;

use Livewire\Component;
use App\Models\SubUser;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SubUserEdit extends Component
{
    public $subUser;
    public $doctor_id;
    public $user_id;
    public $status;
    public $doctors;
    public $users;

    public function mount($id)
    {
        $this->subUser = SubUser::findOrFail($id);
        $this->doctor_id = $this->subUser->doctor_id;
        $this->user_id = $this->subUser->user_id;
        $this->status = $this->subUser->status;
        $this->doctors = Doctor::all();
        $this->users = User::all();
    }

    public function update()
    {
        try {
            $validated = $this->validate([
                'doctor_id' => 'required|exists:doctors,id',
                'user_id' => 'required|exists:users,id|unique:sub_users,user_id,' . $this->subUser->id . ',id,doctor_id,' . $this->doctor_id,
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

            $this->subUser->update($validated);

            $this->dispatch('show-alert', [
                'type' => 'success',
                'message' => 'کاربر زیرمجموعه با موفقیت به‌روزرسانی شد!'
            ]);

            return redirect()->route('admin.panel.sub-users.index');

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->dispatch('show-alert', [
                'type' => 'error',
                'message' => $e->validator->errors()->first()
            ]);
        }
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName, [
            'doctor_id' => 'required|exists:doctors,id',
            'user_id' => 'required|exists:users,id|unique:sub_users,user_id,' . $this->subUser->id . ',id,doctor_id,' . $this->doctor_id,
            'status' => 'required|in:active,inactive',
        ]);
    }

    public function render()
    {
        return view('livewire.admin.panel.sub-users.sub-user-edit');
    }
}
