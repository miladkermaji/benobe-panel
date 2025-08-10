<?php

namespace App\Livewire\Admin\Panel\Contact;

use App\Models\ContactMessage;
use Livewire\Component;

class ContactShow extends Component
{
    public $contact;
    public $adminReply = '';
    public $status = '';

    protected $rules = [
        'adminReply' => 'required|string|min:10',
        'status' => 'required|in:new,read,replied,closed',
    ];

    protected $messages = [
        'adminReply.required' => 'پاسخ الزامی است.',
        'adminReply.min' => 'پاسخ باید حداقل 10 کاراکتر باشد.',
        'status.required' => 'وضعیت الزامی است.',
        'status.in' => 'وضعیت نامعتبر است.',
    ];

    public function mount($id)
    {
        $this->contact = ContactMessage::findOrFail($id);
        $this->adminReply = $this->contact->admin_reply ?? '';
        $this->status = $this->contact->status;
    }

    public function updateStatus($newStatus)
    {
        $this->status = $newStatus;
        $this->saveChanges();
    }

    public function saveReply()
    {
        $this->validate();

        try {
            $this->contact->update([
                'admin_reply' => $this->adminReply,
                'status' => 'replied',
                'replied_at' => now(),
            ]);

            $this->status = 'replied';
            $this->dispatch('show-alert', type: 'success', message: 'پاسخ با موفقیت ذخیره شد!');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره پاسخ: ' . $e->getMessage());
        }
    }

    public function saveChanges()
    {
        try {
            $this->contact->update([
                'status' => $this->status,
            ]);

            $this->dispatch('show-alert', type: 'success', message: 'تغییرات با موفقیت ذخیره شد!');
        } catch (\Exception $e) {
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ذخیره تغییرات: ' . $e->getMessage());
        }
    }

    public function backToList()
    {
        return redirect()->route('admin.panel.contact.index');
    }

    public function render()
    {
        return view('livewire.admin.panel.contact.contact-show');
    }
}
