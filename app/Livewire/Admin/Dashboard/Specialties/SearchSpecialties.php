<?php

namespace App\Livewire\Admin\Dashboard\Specialties;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Dashboard\Specialty\Specialty;

class SearchSpecialties extends Component
{
    use WithPagination;

    public $search = ''; // مقدار جستجو
    public $selectedRows = [];
    public $selectAll = false;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['updateDeleteButton' => 'refreshDeleteButton', 'doDeleteSelected' => 'deleteSelected'];

    public function searchUpdated()
    {
        $this->resetPage(); // هنگام تغییر مقدار جستجو، صفحه را ریست می‌کند
    }

    public function updatedSelectAll($value)
    {
        $this->selectedRows = $value ? Specialty::pluck('id')->toArray() : [];
        $this->dispatch('refreshDeleteButton', hasSelectedRows: count($this->selectedRows) > 0);
    }

    public function toggleStatus($id)
    {
        $specialty = Specialty::find($id);
        if ($specialty) {
            $specialty->status = !$specialty->status;
            $specialty->save();
            $this->dispatch('show-toastr', type: 'success', message: 'وضعیت تخصص با موفقیت تغییر کرد.');
        }
    }

    public function confirmDelete()
    {
        if (count($this->selectedRows) > 0) {
            $this->dispatch('show-delete-confirmation');
        }
    }

    public function deleteSelected()
    {
        if (count($this->selectedRows) > 0) {
            Specialty::whereIn('id', $this->selectedRows)->delete();
            $this->selectedRows = [];
            $this->selectAll = false;
            $this->dispatch('refreshDeleteButton', hasSelectedRows: false);
            $this->dispatch('show-toastr', type: 'success', message: 'تخصص‌های انتخاب‌شده با موفقیت حذف شدند.');
        }
    }

    public function render()
    {
        \Log::info("جستجوی تخصص: " . $this->search);

        $specialties = Specialty::where('name', 'like', '%' . $this->search . '%')->paginate(10);
        return view('livewire.admin.dashboard.specialties.search-specialties', compact('specialties'));
    }
}
