<?php

namespace App\Livewire\Admin\Panel\Subusers;

use Livewire\Component;
use App\Models\Doctor;
use App\Models\SubUser;

class SubUserList extends Component
{
    public $search = '';
    public $expandedDoctors = [];
    public $readyToLoad = false;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
    }

    public function loadSubUsers()
    {
        $this->readyToLoad = true;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
    }

    public function toggleStatus($id)
    {
        $item = SubUser::findOrFail($id);
        $item->update(['status' => $item->status === 'active' ? 'inactive' : 'active']);
        $this->dispatch('show-alert', type: $item->status === 'active' ? 'success' : 'info', message: $item->status === 'active' ? 'فعال شد!' : 'غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteSubUser($id)
    {
        $item = SubUser::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'کاربر زیرمجموعه حذف شد!');
    }

    public function render()
    {
        $doctors = $this->readyToLoad
            ? Doctor::with(['subUsers' => function (
                $query
            ) {
                $query->whereHasMorph('subuserable', [\App\Models\User::class], function ($q) {
                    $q->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
                })->with('subuserable');
            }])
            ->where(function ($query) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                      ->orWhere('last_name', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })
            ->get()
            : collect();

        return view('livewire.admin.panel.sub-users.sub-user-list', [
            'doctors' => $doctors,
        ]);
    }
}
