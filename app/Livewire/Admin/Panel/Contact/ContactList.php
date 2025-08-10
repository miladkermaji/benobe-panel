<?php

namespace App\Livewire\Admin\Panel\Contact;

use App\Models\ContactMessage;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class ContactList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteContactConfirmed' => 'deleteContact',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'updateStatusConfirmed' => 'updateStatusConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedContacts = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadContacts()
    {
        $this->readyToLoad = true;
    }

    public function confirmUpdateStatus($id, $newStatus)
    {
        $item = ContactMessage::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پیام یافت نشد.');
            return;
        }

        $statusText = match($newStatus) {
            'read' => 'خوانده شده',
            'replied' => 'پاسخ داده شده',
            'closed' => 'بسته شده',
            default => 'نامشخص'
        };

        $this->dispatch('confirm-update-status', id: $id, status: $newStatus, statusText: $statusText);
    }

    public function updateStatusConfirmed($id, $status)
    {
        $item = ContactMessage::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پیام یافت نشد.');
            return;
        }

        $item->update(['status' => $status]);
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت پیام با موفقیت تغییر کرد!');

        Cache::forget('contacts_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $item = ContactMessage::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پیام یافت نشد.');
            return;
        }

        $this->dispatch('confirm-delete', id: $id, name: $item->subject);
    }

    public function deleteContact($id)
    {
        $item = ContactMessage::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پیام یافت نشد.');
            return;
        }

        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'پیام با موفقیت حذف شد!');

        Cache::forget('contacts_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedContacts = $this->getContactsQuery()->pluck('id')->map(fn ($id) => (string) $id);
        } else {
            $this->selectedContacts = [];
        }
    }

    public function updatedSelectedContacts()
    {
        $this->selectAll = false;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered) {
            $contactsToDelete = $this->getContactsQuery()->pluck('id');
        } else {
            $contactsToDelete = $this->selectedContacts;
        }

        if (empty($contactsToDelete)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مورد را انتخاب کنید.');
            return;
        }

        $count = ContactMessage::whereIn('id', $contactsToDelete)->delete();

        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->groupAction = '';

        $this->dispatch('show-alert', type: 'success', message: "{$count} پیام با موفقیت حذف شد!");

        Cache::forget('contacts_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedContacts) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مورد را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'mark_read':
                $this->updateStatus('read');
                break;
            case 'mark_replied':
                $this->updateStatus('replied');
                break;
            case 'mark_closed':
                $this->updateStatus('closed');
                break;
            case 'delete':
                $this->dispatch('confirm-delete-selected', allFiltered: $this->applyToAllFiltered);
                break;
            default:
                $this->dispatch('show-alert', type: 'warning', message: 'عملیات نامعتبر انتخاب شده است.');
                break;
        }
    }

    private function updateStatus($status)
    {
        if ($this->applyToAllFiltered) {
            $contactsToUpdate = $this->getContactsQuery()->pluck('id');
        } else {
            $contactsToUpdate = $this->selectedContacts;
        }

        if (empty($contactsToUpdate)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفاً حداقل یک مورد را انتخاب کنید.');
            return;
        }

        $count = ContactMessage::whereIn('id', $contactsToUpdate)->update(['status' => $status]);

        $this->selectedContacts = [];
        $this->selectAll = false;
        $this->groupAction = '';

        $statusText = match($status) {
            'read' => 'خوانده شده',
            'replied' => 'پاسخ داده شده',
            'closed' => 'بسته شده',
            default => 'نامشخص'
        };

        $this->dispatch('show-alert', type: 'success', message: "{$count} پیام به عنوان {$statusText} علامت‌گذاری شد!");

        Cache::forget('contacts_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    private function getContactsQuery()
    {
        $query = ContactMessage::query();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('full_name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%')
                  ->orWhere('subject', 'like', '%' . $this->search . '%')
                  ->orWhere('message', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query;
    }

    public function render()
    {
        if (!$this->readyToLoad) {
            return view('livewire.admin.panel.contact.contact-list', [
                'contacts' => collect([]),
                'totalCount' => 0,
                'filteredCount' => 0,
            ]);
        }

        $query = $this->getContactsQuery();
        $totalCount = ContactMessage::count();
        $filteredCount = $query->count();
        $this->totalFilteredCount = $filteredCount;

        $contacts = $query->ordered()->paginate($this->perPage);

        return view('livewire.admin.panel.contact.contact-list', [
            'contacts' => $contacts,
            'totalCount' => $totalCount,
            'filteredCount' => $filteredCount,
        ]);
    }
}
