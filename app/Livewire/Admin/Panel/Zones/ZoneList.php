<?php

namespace App\Livewire\Admin\Panel\Zones;

use App\Models\Zone;
use Livewire\Component;
use Livewire\WithPagination;

class ZoneList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteZoneConfirmed' => 'deleteZone'];

    public $perPage       = 100;
    public $search        = '';
    public $readyToLoad   = false;
    public $selectedZones = [];
    public $selectAll     = false;
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

    public function loadZones()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Zone::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteZone($id)
    {
        $item = Zone::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'منطقه حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds      = $this->getZonesQuery()->pluck('id')->toArray();
        $this->selectedZones = $value ? $currentPageIds : [];
    }

    public function updatedSelectedZones()
    {
        $currentPageIds  = $this->getZonesQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedZones) && count(array_diff($currentPageIds, $this->selectedZones)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedZones)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ منطقه‌ای انتخاب نشده است.');
            return;
        }

        Zone::whereIn('id', $this->selectedZones)->delete();
        $this->selectedZones = [];
        $this->selectAll     = false;
        $this->dispatch('show-alert', type: 'success', message: 'مناطق انتخاب‌شده حذف شدند!');
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
                $query = $this->getZonesQuery(false);
                $query->delete();
                $this->selectedZones = [];
                $this->selectAll = false;
                $this->applyToAllFiltered = false;
                $this->groupAction = '';
                $this->resetPage();
                $this->dispatch('show-alert', type: 'success', message: 'همه استان‌های فیلترشده حذف شدند!');
                return;
            }
            if (empty($this->selectedZones)) {
                return;
            }
            \App\Models\Zone::whereIn('id', $this->selectedZones)->delete();
            $this->selectedZones = [];
            $this->selectAll = false;
            $this->dispatch('show-alert', type: 'success', message: 'استان‌های انتخاب شده با موفقیت حذف شدند!');
        } elseif ($this->groupAction === 'status_active') {
            $query = $this->applyToAllFiltered ? $this->getZonesQuery(false) : \App\Models\Zone::whereIn('id', $this->selectedZones);
            $query->update(['status' => true]);
            $this->dispatch('show-alert', type: 'success', message: 'استان‌ها فعال شدند!');
        } elseif ($this->groupAction === 'status_inactive') {
            $query = $this->applyToAllFiltered ? $this->getZonesQuery(false) : \App\Models\Zone::whereIn('id', $this->selectedZones);
            $query->update(['status' => false]);
            $this->dispatch('show-alert', type: 'info', message: 'استان‌ها غیرفعال شدند!');
        }
        $this->groupAction = '';
        $this->applyToAllFiltered = false;
        $this->selectedZones = [];
        $this->selectAll = false;
        $this->resetPage();
    }

    public function updatedApplyToAllFiltered($value)
    {
        // No-op, just for UI
    }

    private function getZonesQuery($paginate = true)
    {
        $query = \App\Models\Zone::query()->where('level', 1);
        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }
        if ($this->statusFilter === 'active') {
            $query->where('status', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('status', false);
        }
        $query->orderBy('sort');
        return $paginate ? $query->paginate($this->perPage) : $query;
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getZonesQuery() : null;
        $this->totalFilteredCount = $this->readyToLoad ? $this->getZonesQuery(false)->count() : 0;
        return view('livewire.admin.panel.zones.zone-list', [
            'zones' => $items,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
