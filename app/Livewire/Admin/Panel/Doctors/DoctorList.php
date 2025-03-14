<?php
namespace App\Livewire\Admin\Panel\doctors;

use App\Models\Doctor;
use Livewire\Component;
use Livewire\WithPagination;

class DoctorList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorConfirmed' => 'deleteDoctor'];

    public $perPage         = 10;
    public $search          = '';
    public $readyToLoad     = false;
    public $selecteddoctors = [];
    public $selectAll       = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loaddoctors()
    {
        $this->readyToLoad = true;
    }

    public function toggleStatus($id)
    {
        $item = Doctor::findOrFail($id);
        $item->update(['status' => ! $item->status]);
        $this->dispatch('show-alert', type: $item->status ? 'success' : 'info', message: $item->status ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctor($id)
    {
        $item = Doctor::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'doctor حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds        = $this->getdoctorsQuery()->pluck('id')->toArray();
        $this->selecteddoctors = $value ? $currentPageIds : [];
    }

    public function updatedSelecteddoctors()
    {
        $currentPageIds  = $this->getdoctorsQuery()->pluck('id')->toArray();
        $this->selectAll = ! empty($this->selecteddoctors) && count(array_diff($currentPageIds, $this->selecteddoctors)) === 0;
    }

    public function deleteSelected()
    {
        if (empty($this->selecteddoctors)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ doctor انتخاب نشده است.');
            return;
        }

        Doctor::whereIn('id', $this->selecteddoctors)->delete();
        $this->selecteddoctors = [];
        $this->selectAll       = false;
        $this->dispatch('show-alert', type: 'success', message: 'doctors انتخاب‌شده حذف شدند!');
    }

    private function getdoctorsQuery()
    {
       return Doctor::where('first_name', 'like', '%' . $this->search . '%')
    ->orWhere('last_name', 'like', '%' . $this->search . '%')
    ->orWhere('email', 'like', '%' . $this->search . '%')
    ->orWhere('mobile', 'like', '%' . $this->search . '%')
    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
    ->with(['province', 'city'])
    ->paginate($this->perPage);


    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getdoctorsQuery() : null;

        return view('livewire.admin.panel.doctors.doctor-list', [
            'doctors' => $items,
        ]);
    }
}
