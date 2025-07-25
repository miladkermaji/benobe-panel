<?php

namespace App\Livewire\Admin\Panel\DoctorComments;

use Livewire\Component;
use App\Models\DoctorComment;
use App\Models\Doctor;
use Illuminate\Support\Facades\Cache;

class DoctorCommentCreate extends Component
{
    public $doctor_id;
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
            'comment' => 'required|string',
            'status' => 'boolean',
        ], [
            'doctor_id.required' => 'لطفاً پزشک را انتخاب کنید.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'comment.required' => 'لطفاً متن نظر را وارد کنید.',
        ]);

        $validated['ip_address'] = request()->ip();
        DoctorComment::create($validated);
        Cache::forget('doctor_comments__status__page_1');

        $this->dispatch('show-alert', type: 'success', message: 'نظر با موفقیت ثبت شد!');
        return redirect()->route('admin.panel.doctor-comments.index');
    }

    public function render()
    {
        $this->dispatch('reinit-select2');
        return view('livewire.admin.panel.doctor-comments.doctor-comment-create');
    }
}
