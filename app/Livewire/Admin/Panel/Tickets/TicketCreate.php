<?php

namespace App\Livewire\Admin\Panel\Tickets;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Doctor;
use Livewire\Component;

class TicketCreate extends Component
{
    public $title = '';
    public $description = '';
    public $user_id = '';
    public $doctor_id = '';
    public $status = 'open';

    protected $rules = [
        'title' => 'required|string|max:255',
        'description' => 'required|string',
        'user_id' => 'nullable|exists:users,id',
        'doctor_id' => 'nullable|exists:doctors,id',
        'status' => 'required|in:open,pending,answered,closed',
    ];

    public function submit()
    {
        $messages = [
            'title.required' => 'لطفاً عنوان تیکت را وارد کنید.',
            'title.string' => 'عنوان تیکت باید متن باشد.',
            'title.max' => 'عنوان تیکت نمی‌تواند بیشتر از ۲۵۵ کاراکتر باشد.',
            'description.required' => 'لطفاً توضیحات تیکت را وارد کنید.',
            'description.string' => 'توضیحات باید متن باشد.',
            'user_id.exists' => 'کاربر انتخاب‌شده معتبر نیست.',
            'doctor_id.exists' => 'پزشک انتخاب‌شده معتبر نیست.',
            'status.required' => 'لطفاً وضعیت تیکت را انتخاب کنید.',
            'status.in' => 'وضعیت انتخاب‌شده معتبر نیست.',
        ];
        $this->validate($this->rules, $messages);
        Ticket::create([
            'title' => $this->title,
            'description' => $this->description,
            'user_id' => $this->user_id,
            'doctor_id' => $this->doctor_id,
            'status' => $this->status,
        ]);
        $this->reset(['title', 'description', 'user_id', 'doctor_id', 'status']);
        $this->status = 'open';
        $this->dispatch('show-alert', type: 'success', message: 'تیکت جدید با موفقیت ثبت شد!');
        return redirect()->route('admin.panel.tickets.index');
    }

    public function render()
    {
        $users = User::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();
        $doctors = Doctor::select('id', 'first_name', 'last_name')->orderBy('first_name')->get();
        return view('livewire.admin.panel.tickets.ticket-create', compact('users', 'doctors'));
    }
}
