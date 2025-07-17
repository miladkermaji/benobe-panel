<?php

namespace App\Livewire\Admin\Panel\UserAppointmentFees;

use App\Models\UserAppointmentFee;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentFeeList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteUserAppointmentFeeConfirmed' => 'deleteUserAppointmentFee', 'deleteSelectedConfirmed' => 'deleteSelected'];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedFees = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $sortField = 'id';
    public $sortDirection = 'desc';

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
        'sortField' => ['except' => 'id'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadFees()
    {
        $this->readyToLoad = true;
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function toggleStatus($id)
    {
        $fee = UserAppointmentFee::findOrFail($id);
        $fee->update(['status' => !$fee->status]);
        $this->dispatch('show-alert', type: $fee->status ? 'success' : 'info', message: $fee->status ? 'فعال شد!' : 'غیرفعال شد!');
        Cache::forget('fees_' . $this->search . '_page_' . $this->getPage());
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteUserAppointmentFee($id)
    {
        $fee = UserAppointmentFee::findOrFail($id);
        $fee->delete();
        $this->dispatch('show-alert', type: 'success', message: 'هزینه نوبت با موفقیت حذف شد!');
        Cache::forget('fees_' . $this->search . '_page_' . $this->getPage());
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
        $currentPageIds = $this->getFeesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectedFees = $value ? $currentPageIds : [];
    }

    public function updatedSelectedFees()
    {
        $currentPageIds = $this->getFeesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedFees) && count(array_diff($currentPageIds, $this->selectedFees)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getFeesQuery();
            $query->delete();
            $this->selectedFees = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه هزینه‌های فیلترشده حذف شدند!');
            Cache::forget('fees_' . $this->search . '_page_' . $this->getPage());
            return;
        }
        if (empty($this->selectedFees)) {
            return;
        }
        UserAppointmentFee::whereIn('id', $this->selectedFees)->delete();
        $this->selectedFees = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'هزینه‌های انتخاب شده با موفقیت حذف شدند!');
        Cache::forget('fees_' . $this->search . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedFees) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ هزینه‌ای انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getFeesQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه هزینه‌های فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه هزینه‌های فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedFees = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('fees_' . $this->search . '_page_' . $this->getPage());
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
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
        UserAppointmentFee::whereIn('id', $this->selectedFees)
            ->update(['status' => $status]);

        $this->selectedFees = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت هزینه‌های انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('fees_' . $this->search . '_page_' . $this->getPage());
    }

    protected function getFeesQuery()
    {
        return UserAppointmentFee::query()
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->where('name', 'like', "%$search%");
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('status', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('status', false);
                }
            })
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $query = $this->getFeesQuery();
        $this->totalFilteredCount = $this->readyToLoad ? $query->count() : 0;
        return view('livewire.admin.panel.user-appointment-fees.appointment-fee-list', [
            'fees' => $this->readyToLoad ? $query->paginate($this->perPage) : [],
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
