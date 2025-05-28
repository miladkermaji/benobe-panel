<?php

namespace App\Livewire\Admin\Panel\UserAppointmentFees;

use App\Models\UserAppointmentFee;
use Livewire\Component;
use Livewire\WithPagination;

class AppointmentFeeList extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $listeners = ['refreshList' => '$refresh'];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function delete(UserAppointmentFee $fee)
    {
        $fee->delete();
        $this->dispatch('refreshList');
        session()->flash('success', 'حق نوبت با موفقیت حذف شد.');
    }

    public function render()
    {
        $fees = UserAppointmentFee::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->where('user_id', auth('admin')->id())
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return view('livewire.admin.panel.user-appointment-fees.appointment-fee-list', [
            'fees' => $fees
        ]);
    }
}
