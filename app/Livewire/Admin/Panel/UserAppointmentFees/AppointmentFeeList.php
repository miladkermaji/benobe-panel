<?php

namespace App\Livewire\Admin\Panel\UserAppointmentFees;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\UserAppointmentFee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class AppointmentFeeList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'pagination';
    protected $listeners = ['deleteAppointmentFeeConfirmed' => 'delete', 'refreshList' => '$refresh'];

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $perPage = 10;
    public $readyToLoad = false;
    public $selectedFees = [];
    public $selectAll = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
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

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function delete($id)
    {
        $fee = UserAppointmentFee::findOrFail($id);
        $fee->delete();
        Cache::forget('appointment_fees_' . $this->search . '_page_' . $this->getPage());
        $this->dispatch('show-alert', type: 'success', message: 'حق نوبت با موفقیت حذف شد!');
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getFeesQuery()->pluck('id')->toArray();
        $this->selectedFees = $value ? $currentPageIds : [];
    }

    public function updatedSelectedFees()
    {
        $currentPageIds = $this->getFeesQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedFees) && count(array_diff($currentPageIds, $this->selectedFees)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedFees)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ حق نوبتی انتخاب نشده است.');
            return;
        }

        UserAppointmentFee::whereIn('id', $this->selectedFees)->delete();
        Cache::forget('appointment_fees_' . $this->search . '_page_' . $this->getPage());
        $this->selectedFees = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'حق نوبت‌های انتخاب‌شده حذف شدند!');
        $this->resetPage();
    }

    private function getFeesQuery()
    {
        return UserAppointmentFee::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('description', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate($this->perPage);
    }

    public function render()
    {
        $fees = $this->readyToLoad ? $this->getFeesQuery() : null;
        return view('livewire.admin.panel.user-appointment-fees.appointment-fee-list', [
            'fees' => $fees,
        ]);
    }
}
