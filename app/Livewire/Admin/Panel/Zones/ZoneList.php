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

    public $perPage       = 10;
    public $search        = '';
    public $readyToLoad   = false;
    public $selectedZones = [];
    public $selectAll     = false;

    protected $queryString = [
        'search' => ['except' => ''],
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

    private function getZonesQuery()
    {
        return Zone::provinces() // فقط استان‌ها
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('sort')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getZonesQuery() : null;

        return view('livewire.admin.panel.zones.zone-list', [
            'zones' => $items,
        ]);
    }
}
