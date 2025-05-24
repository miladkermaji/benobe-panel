<?php

namespace App\Livewire\Admin\Panel\UserBlockings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserBlocking;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class UserBlockingList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteUserBlockingConfirmed' => 'deleteUserBlocking',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed',
    ];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedUserBlockings = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadUserBlockings()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = UserBlocking::with(['user', 'doctor'])->findOrFail($id);
        $name = $item->user ? $item->user->first_name . ' ' . $item->user->last_name :
                ($item->doctor ? $item->doctor->first_name . ' ' . $item->doctor->last_name : 'نامشخص');
        $action = $item->status ? 'رفع مسدودیت' : 'مسدود کردن';
        $this->dispatch('confirm-toggle-status', id: $id, name: $name, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $item = UserBlocking::findOrFail($id);
        $oldStatus = $item->status;
        $item->update(['status' => !$item->status]);

        if ($item->status && !$oldStatus) {
            $this->dispatch('show-alert', type: 'success', message: 'مسدودیت فعال شد!');
        } elseif (!$item->status && $oldStatus) {
            $this->dispatch('show-alert', type: 'info', message: 'مسدودیت غیرفعال شد!');
        }
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteUserBlocking($id)
    {
        $item = UserBlocking::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'رکورد مسدودیت حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getUserBlockingsQuery()->pluck('id')->toArray();
        $this->selectedUserBlockings = $value ? $currentPageIds : [];
    }

    public function updatedSelectedUserBlockings()
    {
        $currentPageIds = $this->getUserBlockingsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedUserBlockings) && count(array_diff($currentPageIds, $this->selectedUserBlockings)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedUserBlockings)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ رکوردی انتخاب نشده است.');
            return;
        }

        UserBlocking::whereIn('id', $this->selectedUserBlockings)->delete();
        $this->selectedUserBlockings = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'رکوردهای انتخاب‌شده حذف شدند!');
    }

    private function getUserBlockingsQuery()
    {
        $managerId = Auth::guard('manager')->user()->id;

        return UserBlocking::with(['user', 'doctor', 'manager'])
            ->where(function ($query) use ($managerId) {
                $query->where('manager_id', $managerId)
                      ->orWhereNull('manager_id');
            })
            ->where(function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('mobile', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })->orWhereHas('doctor', function ($q) {
                    $q->where('mobile', 'like', '%' . $this->search . '%')
                      ->orWhere('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%');
                })->orWhere('reason', 'like', '%' . $this->search . '%');
            })
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getUserBlockingsQuery() : null;

        return view('livewire.admin.panel.user-blockings.user-blocking-list', [
            'userBlockings' => $items,
        ]);
    }
}
