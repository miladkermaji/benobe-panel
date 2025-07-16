<?php

namespace App\Livewire\Admin\Panel\DoctorComments;

use Livewire\Component;
use App\Models\DoctorComment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Cache;

class DoctorCommentEdit extends Component
{
    public $comment;
    public $doctor_id;
    public $user_name;
    public $user_phone;
    public $comment_text;
    public $status;
    public $doctors;

    public function mount($id)
    {
        $this->comment = DoctorComment::findOrFail($id);
        $this->doctor_id = $this->comment->doctor_id;
        $this->user_name = $this->comment->user_name;
        $this->user_phone = $this->comment->user_phone;
        $this->comment_text = $this->comment->comment;
        $this->status = $this->comment->status;
        $this->doctors = Doctor::select('id', 'first_name', 'last_name')->get();
    }

    public function update()
    {
        $validated = $this->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'user_name' => 'required|string|max:255',
            'user_phone' => 'nullable|string|max:15',
            'comment_text' => 'required|string',
            'status' => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'user_name.required' => 'لطفاً نام کاربر را وارد کنید.',
            'user_name.max' => 'نام کاربر نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'user_phone.max' => 'شماره تماس نمی‌تواند بیشتر از ۱۵ کاراکتر باشد.',
            'comment_text.required' => 'لطفاً متن نظر را وارد کنید.',
        ]);

        $this->comment->update([
            'doctor_id' => $validated['doctor_id'],
            'user_name' => $validated['user_name'],
            'user_phone' => $validated['user_phone'],
            'comment' => $validated['comment_text'],
            'status' => $validated['status'],
        ]);
        Cache::forget('doctor_comments__status__page_1');

        $this->dispatch('show-alert', type: 'success', message: 'نظر با موفقیت به‌روزرسانی شد!');
        return redirect()->route('admin.panel.doctor-comments.index');
    }

    public function render()
    {
        $this->dispatch('reinit-select2');
        return view('livewire.admin.panel.doctor-comments.doctor-comment-edit');
    }
}
