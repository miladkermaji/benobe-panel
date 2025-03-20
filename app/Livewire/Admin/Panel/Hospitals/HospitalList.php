<?php
namespace App\Livewire\Admin\Panel\Hospitals;

use App\Models\Hospital;
use Livewire\Component;
use Livewire\WithPagination;

class HospitalList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteHospitalConfirmed' => 'deleteHospital'];

    public $perPage           = 10;
    public $search            = '';
    public $readyToLoad       = false;
    public $selectedHospitals = [];
    public $selectAll         = false;

    protected $queryString = ['search' => ['except' => '']];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadHospitals()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $hospital = Hospital::findOrFail($id);
        $hospital->update(['is_active' => ! $hospital->is_active]);
        $this->dispatch('show-alert', type: $hospital->is_active ? 'success' : 'info', message: $hospital->is_active ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteHospital($id)
    {
        $hospital = Hospital::findOrFail($id);
        $hospital->delete();
        $this->dispatch('show-alert', type: 'success', message: 'بیمارستان حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds          = $this->getHospitalsQuery()->pluck('id')->toArray();
        $this->selectedHospitals = $value ? $currentPageIds : [];
    }

    public function updatedSelectedHospitals()
    {
        $currentPageIds  = $this->getHospitalsQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedHospitals) && count(array_diff($currentPageIds, $this->selectedHospitals)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedHospitals)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ بیمارستانی انتخاب نشده است.');
            return;
        }

        Hospital::whereIn('id', $this->selectedHospitals)->delete();
        $this->selectedHospitals = [];
        $this->selectAll         = false;
        $this->dispatch('show-alert', type: 'success', message: 'بیمارستان‌های انتخاب‌شده حذف شدند!');
    }

    private function getHospitalsQuery()
    {
        return Hospital::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->with(['doctor', 'province', 'city'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $hospitals = $this->readyToLoad ? $this->getHospitalsQuery() : null;
        return view('livewire.admin.panel.hospitals.hospital-list', ['hospitals' => $hospitals]);
    }
}
