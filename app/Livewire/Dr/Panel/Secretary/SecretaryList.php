<?php

namespace App\Livewire\Dr\Panel\Secretary;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Secretary;
use App\Models\Zone;
use Illuminate\Support\Facades\Auth;
use App\Traits\HasSelectedClinic;

class SecretaryList extends Component
{
    use WithPagination;
    use HasSelectedClinic;

    protected $paginationTheme = 'bootstrap';

    public $perPage = 20;
    public $search = '';
    public $readyToLoad = false;
    public $selectedSecretaries = [];
    public $selectAll = false;
    public $groupAction = '';
    public $showCreate = false;
    public $showEdit = false;
    public $editId = null;
    public $medical_center_id;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    protected $listeners = [
        'refreshSecretaries' => '$refresh',
    ];

    public function mount()
    {
        $this->medical_center_id = $this->getSelectedMedicalCenterId();
    }

    public function loadSecretaries()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getSecretariesQuery()->pluck('id')->toArray();
        $this->selectedSecretaries = $value ? $currentPageIds : [];
    }

    public function updatedSelectedSecretaries()
    {
        $currentPageIds = $this->getSecretariesQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedSecretaries) && count(array_diff($currentPageIds, $this->selectedSecretaries)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedSecretaries)) {
            $this->dispatch('show-toastr', ['type' => 'warning', 'message' => 'هیچ منشی‌ای انتخاب نشده است.']);
            return;
        }
        $secretaries = Secretary::whereIn('id', $this->selectedSecretaries)->get();
        foreach ($secretaries as $secretary) {
            if ($this->groupAction === 'delete') {
                $secretary->delete();
            } elseif ($this->groupAction === 'status_active') {
                $secretary->status = 1;
                $secretary->save();
            } elseif ($this->groupAction === 'status_inactive') {
                $secretary->status = 0;
                $secretary->save();
            }
        }
        $this->selectedSecretaries = [];
        $this->selectAll = false;
        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'عملیات گروهی انجام شد.']);
    }

    public function toggleStatus($id)
    {
        $secretary = Secretary::findOrFail($id);
        $secretary->status = !$secretary->status;
        $secretary->save();
        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'وضعیت منشی تغییر کرد.']);
    }

    public function showCreateModal()
    {
        $this->showCreate = true;
        $this->showEdit = false;
    }

    public function showEditModal($id)
    {
        $this->editId = $id;
        $this->showEdit = true;
        $this->showCreate = false;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('show-delete-confirmation', ['id' => $id]);
    }

    public function doDeleteSelected($id)
    {
        Secretary::findOrFail($id)->delete();
        $this->dispatch('show-toastr', ['type' => 'success', 'message' => 'منشی حذف شد.']);
    }

    private function getSecretariesQuery()
    {
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;
        return Secretary::where('doctor_id', $doctorId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('first_name', 'like', "%{$this->search}%")
                        ->orWhere('last_name', 'like', "%{$this->search}%")
                        ->orWhere('mobile', 'like', "%{$this->search}%")
                        ->orWhere('national_code', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('id');
    }

    public function render()
    {
        $secretaries = Secretary::query()
            ->where('medical_center_id', $this->medical_center_id)
            ->when($this->search, fn ($q) => $q->where(function ($q) {
                $q->where('first_name', 'like', "%{$this->search}%")
                  ->orWhere('last_name', 'like', "%{$this->search}%")
                  ->orWhere('mobile', 'like', "%{$this->search}%")
                  ->orWhere('national_code', 'like', "%{$this->search}%");
            }))
            ->orderByDesc('id')
            ->paginate($this->perPage);
        return view('livewire.dr.panel.secretary.secretary-list', [
            'secretaries' => $secretaries,
        ]);
    }
}
