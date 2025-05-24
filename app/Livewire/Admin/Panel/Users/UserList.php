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

    protected $queryString = [
        'search' => ['except' => ''],
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

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getUsersQuery()->pluck('id')->toArray();
        $this->selectedUsers = $value ? $currentPageIds : [];
    }

    public function updatedSelectedUsers()
    {
        $currentPageIds = $this->getUsersQuery()->pluck('id')->toArray();
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

    protected function getUsersQuery()
    {
        return User::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhere('mobile', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        return view('livewire.admin.panel.users.user-list', [
            'users' => $this->readyToLoad ? $this->getUsersQuery()->paginate($this->perPage) : [],
        ]);
    }
}
