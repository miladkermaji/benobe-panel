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

    protected $listeners = ['deleteUserConfirmed' => 'deleteUser', 'deleteSelectedConfirmed' => 'deleteSelected'];

    public $perPage       = 50;
    public $search        = '';
    public $readyToLoad   = false;
    public $selectedUsers = [];
    public $selectAll     = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $userTypeFilter = 'all'; // new filter: all, doctor, secretary, patient

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

    public function updatedUserTypeFilter()
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

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getUsersQuery();
            $query->delete();
            $this->selectedUsers = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه کاربران فیلترشده حذف شدند!');
            Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
            return;
        }
        if (empty($this->selectedUsers)) {
            return;
        }
        \App\Models\User::whereIn('id', $this->selectedUsers)->delete();
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
                    // SweetAlert تایید حذف گروهی همه فیلترشده‌ها
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
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
                // SweetAlert تایید حذف گروهی انتخاب شده‌ها
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
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
        Cache::forget('users_' . $this->search . '_page_' . $this->getPage());
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
            ->when($this->userTypeFilter, function ($query) {
                if ($this->userTypeFilter === 'doctor') {
                    $query->where('user_type', 'doctor');
                } elseif ($this->userTypeFilter === 'secretary') {
                    $query->where('user_type', 'secretary');
                } elseif ($this->userTypeFilter === 'patient') {
                    $query->where('user_type', 'patient');
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $perPage = $this->perPage;
        $search = trim($this->search);
        $statusFilter = $this->statusFilter;
        $userTypeFilter = $this->userTypeFilter;

        if ($userTypeFilter === 'doctor') {
            $query = \App\Models\Doctor::query();
            if ($search) {
                if (str_contains($search, ' ')) {
                    [$first, $last] = array_pad(explode(' ', $search, 2), 2, null);
                    $query->where('first_name', 'like', "%$first%")
                          ->where('last_name', 'like', "%$last%") ;
                } else {
                    $query->where(function ($q) use ($search) {
                        $q->orWhere('first_name', 'like', "%$search%")
                          ->orWhere('last_name', 'like', "%$search%")
                          ->orWhere('mobile', 'like', "%$search%")
                          ->orWhere('email', 'like', "%$search%") ;
                    });
                }
            }
            if ($statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($statusFilter === 'inactive') {
                $query->where('is_active', false);
            }
            $users = $query->orderByDesc('created_at')->paginate($perPage);
            foreach ($users as $item) {
                $item->user_type = 'doctor';
            }
        } elseif ($userTypeFilter === 'secretary') {
            $query = \App\Models\Secretary::query();
            if ($search) {
                if (str_contains($search, ' ')) {
                    [$first, $last] = array_pad(explode(' ', $search, 2), 2, null);
                    $query->where('first_name', 'like', "%$first%")
                          ->where('last_name', 'like', "%$last%") ;
                } else {
                    $query->where(function ($q) use ($search) {
                        $q->orWhere('first_name', 'like', "%$search%")
                          ->orWhere('last_name', 'like', "%$search%")
                          ->orWhere('mobile', 'like', "%$search%")
                          ->orWhere('email', 'like', "%$search%") ;
                    });
                }
            }
            if ($statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($statusFilter === 'inactive') {
                $query->where('is_active', false);
            }
            $users = $query->orderByDesc('created_at')->paginate($perPage);
            foreach ($users as $item) {
                $item->user_type = 'secretary';
            }
        } else { // patient یا all
            $query = User::query();
            if ($search) {
                if (str_contains($search, ' ')) {
                    [$first, $last] = array_pad(explode(' ', $search, 2), 2, null);
                    $query->where('first_name', 'like', "%$first%")
                          ->where('last_name', 'like', "%$last%") ;
                } else {
                    $query->where(function ($q) use ($search) {
                        $q->orWhere('first_name', 'like', "%$search%")
                          ->orWhere('last_name', 'like', "%$search%")
                          ->orWhere('mobile', 'like', "%$search%")
                          ->orWhere('email', 'like', "%$search%") ;
                    });
                }
            }
            if ($statusFilter === 'active') {
                $query->where('status', true);
            } elseif ($statusFilter === 'inactive') {
                $query->where('status', false);
            }
            $users = $query->orderByDesc('created_at')->paginate($perPage);
            foreach ($users as $item) {
                $item->user_type = 'patient';
            }
        }

        $this->totalFilteredCount = $users->total();

        return view('livewire.admin.panel.users.user-list', [
            'users' => $users,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
