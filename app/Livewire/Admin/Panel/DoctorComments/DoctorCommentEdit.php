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
    public $comment_text;
    public $status;
    public $doctors;

    public function mount($id)
    {
        $this->comment = DoctorComment::findOrFail($id);
        $this->doctor_id = $this->comment->doctor_id;
        $this->comment_text = $this->comment->comment;
        $this->status = $this->comment->status;
        $this->doctors = Doctor::select('id', 'first_name', 'last_name')->get();
    }

    public function update()
    {
        $validated = $this->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'comment_text' => 'required|string',
            'status' => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'comment_text.required' => 'لطفاً متن نظر را وارد کنید.',
        ]);
        $this->comment->update([
            'doctor_id' => $validated['doctor_id'],
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
