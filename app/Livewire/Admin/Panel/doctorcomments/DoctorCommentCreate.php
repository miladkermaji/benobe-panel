<?php

namespace App\Livewire\Admin\Panel\DoctorComments;

use Livewire\Component;
use App\Models\DoctorComment;
use App\Models\Doctor;

class DoctorCommentCreate extends Component
{
    public $doctor_id;
    public $user_name;
    public $user_phone;
    public $comment;
    public $status = false;
    public $doctors;

    public function mount()
    {
        $this->doctors = Doctor::select('id', 'first_name', 'last_name')->get();
    }

    public function store()
    {
        $validated = $this->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'user_name' => 'required|string|max:255',
            'user_phone' => 'nullable|string|max:15',
            'comment' => 'required|string',
            'status' => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'user_name.required' => 'لطفاً نام کاربر را وارد کنید.',
            'user_name.max' => 'نام کاربر نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'user_phone.max' => 'شماره تماس نمی‌تواند بیشتر از ۱۵ کاراکتر باشد.',
            'comment.required' => 'لطفاً متن نظر را وارد کنید.',
        ]);

        $validated['ip_address'] = request()->ip();
        DoctorComment::create($validated);

        $this->dispatch('show-alert', type: 'success', message: 'نظر با موفقیت ثبت شد!');
        return redirect()->route('admin.panel.doctorcomments.index');
    }

    public function render()
    {
        $this->dispatch('reinit-select2');
        return view('livewire.admin.panel.doctorcomments.doctorcomment-create');
    }
}
