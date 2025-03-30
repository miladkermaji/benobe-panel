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

    public function toggleStatus($userId)
    {
        $user = User::findOrFail($userId);
        $user->update(['activation' => ! $user->activation]);

        $this->dispatch('show-alert', type: $user->activation ? 'success' : 'info', message: $user->activation ? 'کاربر فعال شد!' : 'کاربر غیرفعال شد!');
        Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete(); // حذف عکس در مدل User مدیریت می‌شود

        Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
        $this->dispatch('show-alert', type: 'success', message: 'کاربر حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds      = $this->getUsersQuery()->pluck('id')->toArray();
        $this->selectedUsers = $value ? $currentPageIds : [];
    }

    public function updatedSelectedUsers()
    {
        $currentPageIds  = $this->getUsersQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedUsers) && count(array_diff($currentPageIds, $this->selectedUsers)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedUsers)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کاربری انتخاب نشده است.');
            return;
        }

        User::whereIn('id', $this->selectedUsers)->delete();
        $this->selectedUsers = [];
        $this->selectAll     = false;
        $this->dispatch('show-alert', type: 'success', message: 'کاربران انتخاب‌شده حذف شدند!');
    }

    private function getUsersQuery()
    {
        return User::where('first_name', 'like', '%' . $this->search . '%')
            ->orWhere('last_name', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orWhere('mobile', 'like', '%' . $this->search . '%')
            ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
            ->with(['province', 'city'])
            ->paginate($this->perPage);

    }

    public function render()
    {
        $users = $this->readyToLoad ? $this->getUsersQuery() : null;

        return view('livewire.admin.panel.users.user-list', [
            'users' => $users,
        ]);
    }
}
