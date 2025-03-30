<?php

namespace App\Livewire\Admin\Panel\Laboratories;

use App\Models\Laboratory;
use Livewire\Component;
use Livewire\WithPagination;

class LaboratoryList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteLaboratoryConfirmed' => 'deleteLaboratory'];

    public $perPage              = 10;
    public $search               = '';
    public $readyToLoad          = false;
    public $selectedLaboratories = [];
    public $selectAll            = false;

    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadLaboratories()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $laboratory = Laboratory::findOrFail($id);
        $laboratory->update(['is_active' => ! $laboratory->is_active]);
        $this->dispatch('show-alert', type: $laboratory->is_active ? 'success' : 'info', message: $laboratory->is_active ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteLaboratory($id)
    {
        $laboratory = Laboratory::findOrFail($id);
        $laboratory->delete();
        $this->dispatch('show-alert', type: 'success', message: 'آزمایشگاه حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds             = $this->getLaboratoriesQuery()->pluck('id')->toArray();
        $this->selectedLaboratories = $value ? $currentPageIds : [];
    }

    public function updatedSelectedLaboratories()
    {
        $currentPageIds  = $this->getLaboratoriesQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedLaboratories) && count(array_diff($currentPageIds, $this->selectedLaboratories)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedLaboratories)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ آزمایشگاهی انتخاب نشده است.');
            return;
        }

        Laboratory::whereIn('id', $this->selectedLaboratories)->delete();
        $this->selectedLaboratories = [];
        $this->selectAll            = false;
        $this->dispatch('show-alert', type: 'success', message: 'آزمایشگاه‌های انتخاب‌شده حذف شدند!');
    }

    private function getLaboratoriesQuery()
    {
        return Laboratory::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->with([
                'doctor'   => fn ($query) => $query->select('id', 'first_name', 'last_name'),
                'province' => fn ($query) => $query->select('id', 'name'),
                'city'     => fn ($query) => $query->select('id', 'name'),
                'gallery', // لود کردن رابطه گالری
            ])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $laboratories = $this->readyToLoad ? $this->getLaboratoriesQuery() : null;
        return view('livewire.admin.panel.laboratories.laboratory-list', ['laboratories' => $laboratories]);
    }
}
