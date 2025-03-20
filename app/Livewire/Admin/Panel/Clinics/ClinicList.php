<?php
namespace App\Livewire\Admin\Panel\Clinics;

use App\Models\Clinic;
use Livewire\Component;
use Livewire\WithPagination;

class ClinicList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteClinicConfirmed' => 'deleteClinic'];

    public $perPage         = 10;
    public $search          = '';
    public $readyToLoad     = false;
    public $selectedClinics = [];
    public $selectAll       = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadClinics()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Clinic::findOrFail($id);
        $item->update(['is_active' => ! $item->is_active]);
        $this->dispatch('show-alert', type: $item->is_active ? 'success' : 'info', message: $item->is_active ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteClinic($id)
    {
        $item = Clinic::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds        = $this->getClinicsQuery()->pluck('id')->toArray();
        $this->selectedClinics = $value ? $currentPageIds : [];
    }

    public function updatedSelectedClinics()
    {
        $currentPageIds  = $this->getClinicsQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedClinics) && count(array_diff($currentPageIds, $this->selectedClinics)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedClinics)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کلینیکی انتخاب نشده است.');
            return;
        }

        Clinic::whereIn('id', $this->selectedClinics)->delete();
        $this->selectedClinics = [];
        $this->selectAll       = false;
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک‌های انتخاب‌شده حذف شدند!');
    }

    private function getClinicsQuery()
    {
        return Clinic::where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->with(['doctor', 'province', 'city'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getClinicsQuery() : null;
        return view('livewire.admin.panel.clinics.clinic-list', [
            'clinics' => $items,
        ]);
    }
}
