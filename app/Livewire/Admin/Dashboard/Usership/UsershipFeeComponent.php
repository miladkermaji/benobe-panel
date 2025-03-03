<?php
namespace App\Livewire\Admin\Dashboard\Usership;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Admin\Dashboard\Membershipfee\MembershipFee;

class UsershipFeeComponent extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedRows = [];
    public $selectAll = false;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['doDeleteSelected' => 'deleteSelected'];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    // وقتی selectAll عوض می‌شه
    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRows = MembershipFee::where('user_type', 'normal')
                ->where('name', 'like', '%' . $this->search . '%')
                ->pluck('id')
                ->toArray();
        } else {
            $this->selectedRows = [];
        }
    }

    // وقتی selectedRows عوض می‌شه
    public function updatedSelectedRows()
    {
        $totalFees = MembershipFee::where('user_type', 'normal')
            ->where('name', 'like', '%' . $this->search . '%')
            ->count();
        $this->selectAll = count($this->selectedRows) === $totalFees && $totalFees > 0;
    }

    public function toggleStatus($id)
    {
        $fee = MembershipFee::find($id);
        $fee->status = !$fee->status;
        $fee->save();
        $this->dispatch('show-toastr', type: 'success', message: 'وضعیت تعرفه تغییر کرد.');

    }

    public function confirmDelete($id = null)
    {
        if ($id) {
            $this->dispatch('show-delete-confirmation', ['id' => $id]);
        } elseif (count($this->selectedRows) > 0) {
            $this->dispatch('show-delete-confirmation');
        }
    }

    public function deleteSelected()
    {
        if (count($this->selectedRows) > 0) {
            MembershipFee::whereIn('id', $this->selectedRows)->delete();
            $this->selectedRows = [];
            $this->selectAll = false;
            $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'تعرفه‌های انتخاب‌شده حذف شدند.']);
        }
    }

    public function render()
    {
        $fees = MembershipFee::where('name', 'like', '%' . $this->search . '%')
            ->where('user_type', 'normal')
            ->paginate(10);

        return view('livewire.admin.dashboard.usership.usership-fee-component', [
            'fees' => $fees,
            'hasSelectedRows' => count($this->selectedRows) > 0,
        ]);
    }

    public function searchUpdated()
    {
        $this->resetPage();
    }
}