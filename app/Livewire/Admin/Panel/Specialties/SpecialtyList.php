<?php

namespace App\Livewire\Admin\Panel\Specialties;

use App\Models\Specialty;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

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
    public $statusFilter = '';
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
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
        Cache::forget('specialties_' . $this->search . '_page_' . $this->getPage());
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
        Cache::forget('specialties_' . $this->search . '_page_' . $this->getPage());
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

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedGroupAction()
    {
        // Optional: reset selection or handle UI
    }

    public function executeGroupAction()
    {
        if ($this->groupAction === 'delete') {
            if ($this->applyToAllFiltered) {
                $query = $this->getSpecialtiesQuery(false);
                $query->delete();
                $this->selectedSpecialties = [];
                $this->selectAll = false;
                $this->applyToAllFiltered = false;
                $this->groupAction = '';
                $this->resetPage();
                $this->dispatch('show-alert', type: 'success', message: 'همه تخصص‌های فیلترشده حذف شدند!');
                Cache::forget('specialties_' . $this->search . '_page_' . $this->getPage());
                return;
            }
            if (empty($this->selectedSpecialties)) {
                return;
            }
            Specialty::whereIn('id', $this->selectedSpecialties)->delete();
            $this->selectedSpecialties = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'تخصص‌های انتخاب شده با موفقیت حذف شدند!');
            Cache::forget('specialties_' . $this->search . '_page_' . $this->getPage());
        } elseif ($this->groupAction === 'status_active') {
            $query = $this->applyToAllFiltered ? $this->getSpecialtiesQuery(false) : Specialty::whereIn('id', $this->selectedSpecialties);
            $query->update(['status' => true]);
            $this->dispatch('show-alert', type: 'success', message: 'تخصص‌ها فعال شدند!');
        } elseif ($this->groupAction === 'status_inactive') {
            $query = $this->applyToAllFiltered ? $this->getSpecialtiesQuery(false) : Specialty::whereIn('id', $this->selectedSpecialties);
            $query->update(['status' => false]);
            $this->dispatch('show-alert', type: 'info', message: 'تخصص‌ها غیرفعال شدند!');
        }
        $this->groupAction = '';
        $this->applyToAllFiltered = false;
        $this->selectedSpecialties = [];
        $this->selectAll = false;
        $this->resetPage();
        Cache::forget('specialties_' . $this->search . '_page_' . $this->getPage());
    }

    public function updatedApplyToAllFiltered($value)
    {
        // No-op, just for UI
    }

    public function deleteSelected()
    {
        if (empty($this->selectedSpecialties)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تخصصی انتخاب نشده است.');
            return;
        }

        Specialty::whereIn('id', $this->selectedSpecialties)->delete();
        Cache::forget('specialties_' . $this->search . '_page_' . $this->getPage());
        $this->selectedSpecialties = [];
        $this->selectAll           = false;
        $this->dispatch('show-alert', type: 'success', message: 'تخصص‌های انتخاب‌شده حذف شدند!');
    }

    private function getSpecialtiesQuery($paginate = true)
    {
        $query = Specialty::query();
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }
        if ($this->statusFilter === 'active') {
            $query->where('status', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('status', false);
        }
        $query->orderBy('name');
        return $paginate ? $query->paginate($this->perPage) : $query;
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getSpecialtiesQuery() : null;
        $this->totalFilteredCount = $this->readyToLoad ? $this->getSpecialtiesQuery(false)->count() : 0;
        return view('livewire.admin.panel.specialties.specialty-list', [
            'specialties' => $items,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
