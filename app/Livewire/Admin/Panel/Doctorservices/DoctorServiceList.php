<?php
namespace App\Livewire\Admin\Panel\DoctorServices;

use App\Models\DoctorService;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorServiceList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorServiceConfirmed' => 'deleteDoctorService'];

    public $perPage                = 10;
    public $search                 = '';
    public $readyToLoad            = false;
    public $selectedDoctorServices = [];
    public $selectAll              = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDoctorServices()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorService($id)
    {
        $item = DoctorService::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'خدمت پزشک حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds               = $this->getDoctorServicesQuery()->pluck('id')->toArray();
        $this->selectedDoctorServices = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDoctorServices()
    {
        $currentPageIds  = $this->getDoctorServicesQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selectedDoctorServices) && count(array_diff($currentPageIds, $this->selectedDoctorServices)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selectedDoctorServices)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ خدمت پزشکی انتخاب نشده است.');
            return;
        }

        DoctorService::whereIn('id', $this->selectedDoctorServices)->delete();
        $this->selectedDoctorServices = [];
        $this->selectAll              = false;
        $this->dispatch('show-alert', type: 'success', message: 'خدمات پزشکی انتخاب‌شده حذف شدند!');
    }

    private function getDoctorServicesQuery()
    {
        return DoctorService::with(['doctor', 'parent'])
            ->where('name', 'like', '%' . $this->search . '%')
            ->orWhere('description', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getDoctorServicesQuery() : null;

        return view('livewire.admin.panel.doctorservices.doctorservice-list', [
            'doctorservices' => $items,
        ]);
    }
}
