<?php

namespace App\Livewire\Admin\Panel\Tools;

use App\Models\Newsletter;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class NewsLatter extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteMemberConfirmed' => 'deleteMember'];

    public $search = '';
    public $newEmail = '';
    public $editId = null;
    public $editEmail = '';
    public $perPage = 100;
    public $selectedMembers = [];
    public $selectAll = false;
    public $readyToLoad = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected $rules = [
        'newEmail'  => 'required|email|unique:newsletters,email',
        'editEmail' => 'required|email',
    ];

    // پیام‌های اعتبارسنجی فارسی
    protected $messages = [
        'newEmail.required'  => 'لطفاً ایمیل را وارد کنید.',
        'newEmail.email'     => 'ایمیل واردشده معتبر نیست.',
        'newEmail.unique'    => 'این ایمیل قبلاً ثبت شده است.',
        'editEmail.required' => 'لطفاً ایمیل را وارد کنید.',
        'editEmail.email'    => 'ایمیل واردشده معتبر نیست.',
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadNewsletterMembers()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getNewsletterQuery()->pluck('id')->toArray();
        $this->selectedMembers = $value ? $currentPageIds : [];
    }

    public function updatedSelectedMembers()
    {
        $currentPageIds = $this->getNewsletterQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedMembers) && count(array_diff($currentPageIds, $this->selectedMembers)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedMembers)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ عضوی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->deleteSelected();
                break;
            case 'status_active':
                $this->updateStatus(true);
                break;
            case 'status_inactive':
                $this->updateStatus(false);
                break;
        }

        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        Newsletter::whereIn('id', $this->selectedMembers)
            ->update(['is_active' => $status]);

        $this->selectedMembers = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت اعضای انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function addMember()
    {
        $this->validateOnly('newEmail');

        try {
            Newsletter::create([
                'email'     => $this->newEmail,
                'is_active' => true,
            ]);
            $this->newEmail = '';
            $this->dispatch('show-alert', type: 'success', message: 'عضو جدید با موفقیت اضافه شد.');
            $this->dispatch('close-modal', modalId: 'addMemberModal');
        } catch (\Exception $e) {
            Log::error('Error adding newsletter member: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در اضافه کردن عضو: ' . $e->getMessage());
        }
    }

    public function startEdit($id)
    {
        $member          = Newsletter::findOrFail($id);
        $this->editId    = $id;
        $this->editEmail = $member->email;
    }

    public function updateMember()
    {
        $this->validate(['editEmail' => "required|email|unique:newsletters,email,{$this->editId}"]);

        try {
            $member = Newsletter::findOrFail($this->editId);
            $member->update(['email' => $this->editEmail]);
            $this->editId    = null;
            $this->editEmail = '';
            $this->dispatch('show-alert', type: 'success', message: 'عضو با موفقیت ویرایش شد.');
        } catch (\Exception $e) {
            Log::error('Error updating newsletter member: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در ویرایش عضو: ' . $e->getMessage());
        }
    }

    public function cancelEdit()
    {
        $this->editId    = null;
        $this->editEmail = '';
    }

    public function toggleStatus($id)
    {
        try {
            $member            = Newsletter::findOrFail($id);
            $member->is_active = !$member->is_active;
            $member->save();
            $this->dispatch('show-alert', type: 'success', message: 'وضعیت عضو با موفقیت تغییر کرد.');
        } catch (\Exception $e) {
            Log::error('Error toggling newsletter status: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در تغییر وضعیت: ' . $e->getMessage());
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteMember($id)
    {
        try {
            $member = Newsletter::findOrFail($id);
            $member->delete();
            $this->selectedMembers = array_diff($this->selectedMembers, [$id]);
            $this->dispatch('show-alert', type: 'success', message: 'عضو با موفقیت حذف شد.');
        } catch (\Exception $e) {
            Log::error('Error deleting newsletter member: ' . $e->getMessage());
            $this->dispatch('show-alert', type: 'error', message: 'خطا در حذف عضو: ' . $e->getMessage());
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedMembers)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ عضوی انتخاب نشده است.');
            return;
        }

        Newsletter::whereIn('id', $this->selectedMembers)->delete();
        $this->selectedMembers = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'اعضای انتخاب‌شده با موفقیت حذف شدند.');
    }

    public function export()
    {
        $members = Newsletter::all();
        $csv     = "ایمیل,وضعیت\n";
        foreach ($members as $member) {
            $csv .= "{$member->email}," . ($member->is_active ? 'فعال' : 'غیرفعال') . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'newsletter-members.csv');
    }

    private function getNewsletterQuery()
    {
        return Newsletter::where('email', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $members = $this->readyToLoad ? $this->getNewsletterQuery() : null;

        return view('livewire.admin.panel.tools.news-latter', [
            'members' => $members,
        ]);
    }
}
