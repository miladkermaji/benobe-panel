<?php

namespace App\Livewire\Admin\Panel\Users;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteUserConfirmed' => 'deleteUser'];

    public $perPage       = 10;
    public $search        = '';
    public $readyToLoad   = false;
    public $selectedUsers = [];
    public $selectAll     = false;
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

    public function loadUsers()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => !$user->status]);
        $this->dispatch('show-alert', type: $user->status ? 'success' : 'info', message: $user->status ? 'فعال شد!' : 'غیرفعال شد!');
        Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        $this->dispatch('show-alert', type: 'success', message: 'کاربر با موفقیت حذف شد!');
        Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
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
        // فقط آی‌دی‌های صفحه فعلی را انتخاب کن
        $currentPageIds = $this->getUsersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedUsers = $value ? $currentPageIds : [];
    }

    public function updatedSelectedUsers()
    {
        // فقط آی‌دی‌های صفحه فعلی را بررسی کن
        $currentPageIds = $this->getUsersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedUsers) && count(array_diff($currentPageIds, $this->selectedUsers)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedUsers)) {
            return;
        }

        User::whereIn('id', $this->selectedUsers)->delete();
        $this->selectedUsers = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'کاربران انتخاب شده با موفقیت حذف شدند!');
        Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedUsers) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کاربری انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getUsersQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $query->delete();
                    $this->dispatch('show-alert', type: 'success', message: 'همه کاربران فیلترشده حذف شدند!');
                    break;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه کاربران فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه کاربران فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedUsers = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
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
        User::whereIn('id', $this->selectedUsers)
            ->update(['status' => $status]);

        $this->selectedUsers = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کاربران انتخاب‌شده با موفقیت تغییر کرد.');
    }

    protected function getUsersQuery()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where(function ($q) use ($search) {
                    // جستجوی ترکیبی نام و نام خانوادگی
                    if (str_contains($search, ' ')) {
                        [$first, $last] = array_pad(explode(' ', $search, 2), 2, null);
                        $q->orWhere(function ($qq) use ($first, $last) {
                            $qq->where('first_name', 'like', "%$first%")
                               ->where('last_name', 'like', "%$last%") ;
                        });
                    }
                    $q->orWhere('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('mobile', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%") ;
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('status', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('status', false);
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $query = $this->getUsersQuery();
        $this->totalFilteredCount = $this->readyToLoad ? $query->count() : 0;
        return view('livewire.admin.panel.users.user-list', [
            'users' => $this->readyToLoad ? $query->paginate($this->perPage) : [],
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
