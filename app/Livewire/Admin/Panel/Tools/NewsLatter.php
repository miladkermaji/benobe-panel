<?php

namespace App\Livewire\Admin\Panel\Tools;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Tools\Newsletter;
use Illuminate\Support\Facades\Log;

class NewsLatter extends Component
{
    use WithPagination;

    public $search = '';
    public $newEmail = '';
    public $editId = null;
    public $editEmail = '';
    public $perPage = 10;
    public $selectedMembers = [];
    public $selectAll = false;

    protected $rules = [
        'newEmail' => 'required|email|unique:newsletters,email',
        'editEmail' => 'required|email',
    ];

    // پیام‌های اعتبارسنجی فارسی
    protected $messages = [
        'newEmail.required' => 'لطفاً ایمیل را وارد کنید.',
        'newEmail.email' => 'ایمیل واردشده معتبر نیست.',
        'newEmail.unique' => 'این ایمیل قبلاً ثبت شده است.',
        'editEmail.required' => 'لطفاً ایمیل را وارد کنید.',
        'editEmail.email' => 'ایمیل واردشده معتبر نیست.',
    ];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = Newsletter::where('email', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectedMembers = $value ? $currentPageIds : [];
    }

    public function updatedSelectedMembers()
    {
        $currentPageIds = Newsletter::where('email', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage)
            ->pluck('id')
            ->toArray();

        $this->selectAll = !empty($this->selectedMembers) && count(array_diff($currentPageIds, $this->selectedMembers)) === 0;
    }

    public function addMember()
    {
        $this->validateOnly('newEmail');

        try {
            Newsletter::create([
                'email' => $this->newEmail,
                'is_active' => true,
            ]);
            $this->newEmail = '';
            $this->dispatch('toast', 'عضو جدید با موفقیت اضافه شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error adding newsletter member: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در اضافه کردن عضو: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function startEdit($id)
    {
        $member = Newsletter::findOrFail($id);
        $this->editId = $id;
        $this->editEmail = $member->email;
    }

    public function updateMember()
    {
        $this->validate(['editEmail' => "required|email|unique:newsletters,email,{$this->editId}"]);

        try {
            $member = Newsletter::findOrFail($this->editId);
            $member->update(['email' => $this->editEmail]);
            $this->editId = null;
            $this->editEmail = '';
            $this->dispatch('toast', 'عضو با موفقیت ویرایش شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error updating newsletter member: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در ویرایش عضو: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function cancelEdit()
    {
        $this->editId = null;
        $this->editEmail = '';
    }

    public function toggleStatus($id)
    {
        try {
            $member = Newsletter::findOrFail($id);
            $member->is_active = !$member->is_active;
            $member->save();
            $this->dispatch('toast', 'وضعیت عضو با موفقیت تغییر کرد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error toggling newsletter status: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در تغییر وضعیت: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteMember($id)
    {
        try {
            $member = Newsletter::findOrFail($id);
            $member->delete();
            $this->selectedMembers = array_diff($this->selectedMembers, [$id]);
            $this->dispatch('toast', 'عضو با موفقیت حذف شد.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting newsletter member: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف عضو: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function deleteSelected()
    {
        if (empty($this->selectedMembers)) {
            $this->dispatch('toast', 'هیچ عضوی انتخاب نشده است.', ['type' => 'warning']);
            return;
        }

        $this->dispatch('confirmDeleteSelected');
    }

    public function confirmDeleteSelected()
    {
        try {
            Newsletter::whereIn('id', $this->selectedMembers)->delete();
            $this->selectedMembers = [];
            $this->selectAll = false;
            $this->dispatch('toast', 'اعضای انتخاب‌شده با موفقیت حذف شدند.', ['type' => 'success']);
        } catch (\Exception $e) {
            Log::error('Error deleting selected members: ' . $e->getMessage());
            $this->dispatch('toast', 'خطا در حذف اعضا: ' . $e->getMessage(), ['type' => 'error']);
        }
    }

    public function export()
    {
        $members = Newsletter::all();
        $csv = "ایمیل,وضعیت\n";
        foreach ($members as $member) {
            $csv .= "{$member->email}," . ($member->is_active ? 'فعال' : 'غیرفعال') . "\n";
        }
        return response()->streamDownload(function () use ($csv) {
            echo $csv;
        }, 'newsletter-members.csv');
    }

    public function render()
    {
        $members = Newsletter::where('email', 'like', '%' . $this->search . '%')
            ->paginate($this->perPage);

        return view('livewire.admin.panel.tools.news-latter', [
            'members' => $members,
        ]);
    }
}