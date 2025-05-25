<?php

namespace App\Livewire\Admin\Panel\Specialties;

use App\Models\Specialty;
use Livewire\Component;
use Livewire\WithPagination;

class SpecialtyList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteSpecialtyConfirmed' => 'deleteSpecialty'];

    public $perPage             = 300;
    public $search              = '';
    public $readyToLoad         = false;
    public $selectedSpecialties = [];
    public $selectAll           = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadSpecialties()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Specialty::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteSpecialty($id)
    {
        $item = Specialty::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تخصص حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds            = $this->getSpecialtiesQuery()->pluck('id')->toArray();
        $this->selectedSpecialties = $value ? $currentPageIds : [];
    }

    public function updatedSelectedSpecialties()
    {
        $currentPageIds  = $this->getSpecialtiesQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedSpecialties) && count(array_diff($currentPageIds, $this->selectedSpecialties)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedSpecialties)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تخصصی انتخاب نشده است.');
            return;
        }

        Specialty::whereIn('id', $this->selectedSpecialties)->delete();
        $this->selectedSpecialties = [];
        $this->selectAll           = false;
        $this->dispatch('show-alert', type: 'success', message: 'تخصص‌های انتخاب‌شده حذف شدند!');
    }

    private function getSpecialtiesQuery()
    {
        return Specialty::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getSpecialtiesQuery() : null;

        return view('livewire.admin.panel.specialties.specialty-list', [
            'specialties' => $items,
        ]);
    }
}
