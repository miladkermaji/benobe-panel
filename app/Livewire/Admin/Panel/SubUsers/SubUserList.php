<?php

namespace App\Livewire\Admin\Panel\Subusers;

use Livewire\Component;
use App\Models\Doctor;
use App\Models\SubUser;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class SubUserList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteSubUserConfirmed' => 'deleteSubUser',
        'deleteSelectedConfirmed' => 'deleteSelected',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedSubUsers = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $expandedDoctors = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadSubUsers()
    {
        $this->readyToLoad = true;
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
        $currentPageIds = $this->getSubUsersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedSubUsers = $value ? $currentPageIds : [];
    }

    public function updatedSelectedSubUsers()
    {
        $currentPageIds = $this->getSubUsersQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedSubUsers) && count(array_diff($currentPageIds, $this->selectedSubUsers)) === 0;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
    }

    public function toggleStatus($id)
    {
        $item = SubUser::findOrFail($id);
        $item->update(['status' => $item->status === 'active' ? 'inactive' : 'active']);
        $this->dispatch('show-alert', type: $item->status === 'active' ? 'success' : 'info', message: $item->status === 'active' ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteSubUser($id)
    {
        $item = \App\Models\SubUser::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'کاربر زیرمجموعه حذف شد!');
        Cache::forget('subusers_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getSubUsersQuery();
            $query->delete();
            $this->selectedSubUsers = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه کاربران زیرمجموعه فیلترشده حذف شدند!');
            Cache::forget('subusers_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }
        if (empty($this->selectedSubUsers)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کاربری انتخاب نشده است.');
            return;
        }
        \App\Models\SubUser::whereIn('id', $this->selectedSubUsers)->delete();
        $this->selectedSubUsers = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'کاربران زیرمجموعه انتخاب شده حذف شدند!');
        Cache::forget('subusers_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedSubUsers) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کاربری انتخاب نشده است.');
            return;
        }
        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }
        if ($this->applyToAllFiltered) {
            $query = $this->getSubUsersQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => 'active']);
                    $this->dispatch('show-alert', type: 'success', message: 'همه کاربران زیرمجموعه فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => 'inactive']);
                    $this->dispatch('show-alert', type: 'success', message: 'همه کاربران زیرمجموعه فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedSubUsers = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('subusers_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }
        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'status_active':
                $this->updateStatus('active');
                break;
            case 'status_inactive':
                $this->updateStatus('inactive');
                break;
        }
        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        \App\Models\SubUser::whereIn('id', $this->selectedSubUsers)
            ->update(['status' => $status]);
        $this->selectedSubUsers = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کاربران زیرمجموعه انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('subusers_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    protected function getSubUsersQuery()
    {
        return \App\Models\SubUser::query()
            ->with('subuserable')
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->whereHasMorph('subuserable', [\App\Models\User::class], function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhere('mobile', 'like', "%$search%") ;
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('status', 'active');
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('status', 'inactive');
                }
            })
            ->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $owners = collect();
        foreach ([
            'App\\Models\\Doctor',
            'App\\Models\\Secretary',
            'App\\Models\\Admin\\Manager',
            'App\\Models\\User',
        ] as $model) {
            if (class_exists($model)) {
                $owners = $owners->concat($model::with(['subUsers' => function ($query) {
                    $query->with('subuserable')
                        ->when($this->search, function ($q) {
                            $search = trim($this->search);
                            $q->whereHasMorph('subuserable', [\App\Models\User::class], function ($qq) use ($search) {
                                $qq->where('first_name', 'like', "%$search%")
                                    ->orWhere('last_name', 'like', "%$search%")
                                    ->orWhere('mobile', 'like', "%$search%") ;
                            });
                        })
                        ->when($this->statusFilter, function ($q) {
                            if ($this->statusFilter === 'active') {
                                $q->where('status', 'active');
                            } elseif ($this->statusFilter === 'inactive') {
                                $q->where('status', 'inactive');
                            }
                        })
                        ->orderBy('created_at', 'desc');
                }])->whereHas('subUsers', function ($query) {
                    if (!empty($this->search)) {
                        $search = trim($this->search);
                        $query->whereHasMorph('subuserable', [\App\Models\User::class], function ($q) use ($search) {
                            $q->where('first_name', 'like', "%$search%")
                                ->orWhere('last_name', 'like', "%$search%")
                                ->orWhere('mobile', 'like', "%$search%") ;
                        });
                    }
                    if ($this->statusFilter === 'active') {
                        $query->where('status', 'active');
                    } elseif ($this->statusFilter === 'inactive') {
                        $query->where('status', 'inactive');
                    }
                })->get());
            }
        }
        $this->totalFilteredCount = $this->readyToLoad ? $this->getSubUsersQuery()->count() : 0;
        return view('livewire.admin.panel.sub-users.sub-user-list', [
            'owners' => $owners,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
