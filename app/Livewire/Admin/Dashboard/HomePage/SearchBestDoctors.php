<?php

namespace App\Livewire\Admin\Dashboard\HomePage;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Dashboard\HomePage\BestDoctor;

class SearchBestDoctors extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedRows = [];
    public $selectAll = false;

    protected $paginationTheme = 'bootstrap';

    // ✅ اضافه کردن متد deleteConfirmed به listeners
    protected $listeners = [
        'updateDeleteButton' => 'checkIfRowsSelected',
        'deleteConfirmed' => 'deleteSelected' // ✅ اضافه شد
    ];


    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function checkIfRowsSelected()
    {
        $this->dispatch('refreshDeleteButton', ['hasSelectedRows' => count($this->selectedRows) > 0]);
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = BestDoctor::pluck('id')->toArray();
        } else {
            $this->selectedRows = [];
        }
        $this->checkIfRowsSelected();
    }

    // ✅ متد تغییر وضعیت پزشک برتر (فعال/غیرفعال)
    public function toggleStatus($id)
    {
        $doctor = BestDoctor::find($id);
        if ($doctor) {
            $doctor->status = !$doctor->status;
            $doctor->save();

            $this->dispatch('show-toastr', type : 'success', message : 'وضعیت با موفقیت تغییر کرد.');
        }
    }

    public function confirmDelete()
    {
        if (count($this->selectedRows) > 0) {
            $this->dispatch('show-delete-confirmation');
        }
    }

    // ✅ اصلاح متد حذف
    public function deleteSelected($id = null)
    {
        if ($id) {
            BestDoctor::find($id)?->delete();
        } else {
            BestDoctor::whereIn('id', $this->selectedRows)->delete();
            $this->selectedRows = [];
            $this->selectAll = false;
            $this->dispatch('refreshDeleteButton', ['hasSelectedRows' => false]);
        }

        // ارسال پیام به توستر با مقدار مشخص
        $this->dispatch('show-toastr', type: 'success', message: 'پزشک(ان) برتر با موفقیت حذف شد.');
    }


    public function render()
    {
        $bestDoctors = BestDoctor::whereHas('doctor', function ($query) {
            $query->where('first_name', 'like', "%{$this->search}%")
                ->orWhere('last_name', 'like', "%{$this->search}%");
        })->paginate(10);

        return view('livewire.admin.dashboard.home_page.search-best-doctors', compact('bestDoctors'));
    }

    public function searchUpdated()
    {
        $this->resetPage();
    }
}
