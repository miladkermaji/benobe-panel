<?php

namespace App\Livewire\Admin\Panel\TreatmentCenters;

use App\Models\TreatmentCenter;
use Livewire\Component;
use Livewire\WithPagination;

class TreatmentCenterList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteTreatmentCenterConfirmed' => 'deleteTreatmentCenter'];

    public $perPage                  = 10;
    public $search                   = '';
    public $readyToLoad              = false;
    public $selectedTreatmentCenters = [];
    public $selectAll                = false;

    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadTreatmentCenters()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $center = TreatmentCenter::findOrFail($id);
        $center->update(['is_active' => ! $center->is_active]);
        $this->dispatch('show-alert', type: $center->is_active ? 'success' : 'info', message: $center->is_active ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteTreatmentCenter($id)
    {
        $center = TreatmentCenter::findOrFail($id);
        $center->delete();
        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds                 = $this->getTreatmentCentersQuery()->pluck('id')->toArray();
        $this->selectedTreatmentCenters = $value ? $currentPageIds : [];
    }

    public function updatedSelectedTreatmentCenters()
    {
        $currentPageIds  = $this->getTreatmentCentersQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedTreatmentCenters) && count(array_diff($currentPageIds, $this->selectedTreatmentCenters)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedTreatmentCenters)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ درمانگاهی انتخاب نشده است.');
            return;
        }

        TreatmentCenter::whereIn('id', $this->selectedTreatmentCenters)->delete();
        $this->selectedTreatmentCenters = [];
        $this->selectAll                = false;
        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه‌های انتخاب‌شده حذف شدند!');
    }

    private function getTreatmentCentersQuery()
    {
        return TreatmentCenter::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->with([
                'doctor'   => fn ($query) => $query->select('id', 'first_name', 'last_name'),
                'province' => fn ($query) => $query->select('id', 'name'),
                'city'     => fn ($query) => $query->select('id', 'name'),
            ])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $centers = $this->readyToLoad ? $this->getTreatmentCentersQuery() : null;
        return view('livewire.admin.panel.treatmentcenters.treatmentcenters-list', ['treatmentCenters' => $centers]);
    }
}
