<?php

namespace App\Livewire\Admin\Panel\Secretaries;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Doctor;
use App\Models\Secretary;
use Illuminate\Support\Facades\Cache;

class SecretaryList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteSecretaryConfirmed' => 'deleteSecretary',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleActiveConfirmed' => 'toggleActiveConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedSecretaries = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $expandedDoctors = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadSecretaries()
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

    public function confirmToggleActive($id)
    {
        $secretary = Secretary::find($id);
        if (!$secretary) {
            $this->dispatch('show-alert', type: 'error', message: 'منشی یافت نشد.');
            return;
        }
        $name = $secretary->first_name . ' ' . $secretary->last_name;
        $action = $secretary->is_active ? 'غیرفعال' : 'فعال';
        $this->dispatch('confirm-toggle-active', id: $id, name: $name, action: $action);
    }

    public function toggleActiveConfirmed($id)
    {
        $secretary = Secretary::find($id);
        if (!$secretary) {
            $this->dispatch('show-alert', type: 'error', message: 'منشی یافت نشد.');
            return;
        }
        $secretary->update(['is_active' => !$secretary->is_active]);
        $this->dispatch('show-alert', type: 'success', message: $secretary->is_active ? 'منشی فعال شد!' : 'منشی غیرفعال شد!');
        Cache::forget('secretaries_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteSecretary($id)
    {
        $secretary = Secretary::find($id);
        if (!$secretary) {
            $this->dispatch('show-alert', type: 'error', message: 'منشی یافت نشد.');
            return;
        }
        $secretary->delete();
        $this->dispatch('show-alert', type: 'success', message: 'منشی با موفقیت حذف شد!');
        Cache::forget('secretaries_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = Cache::remember('secretaries_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getSecretariesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectedSecretaries = $value ? $currentPageIds : [];
    }

    public function updatedSelectedSecretaries()
    {
        $currentPageIds = Cache::remember('secretaries_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getSecretariesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectAll = !empty($this->selectedSecretaries) && count(array_diff($currentPageIds, $this->selectedSecretaries)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getSecretariesQuery();
            $secretaries = $query->get();
            foreach ($secretaries as $secretary) {
                $secretary->delete();
            }
            $this->selectedSecretaries = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه منشی‌های فیلترشده حذف شدند!');
            Cache::forget('secretaries_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        if (empty($this->selectedSecretaries)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ منشی‌ای انتخاب نشده است.');
            return;
        }

        $secretaries = Secretary::whereIn('id', $this->selectedSecretaries)->get();
        foreach ($secretaries as $secretary) {
            $secretary->delete();
        }
        $this->selectedSecretaries = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'منشی‌های انتخاب‌شده حذف شدند!');
        Cache::forget('secretaries_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedSecretaries) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ منشی‌ای انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getSecretariesQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'set_active':
                    $query->update(['is_active' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه منشی‌های فیلترشده فعال شدند!');
                    break;
                case 'unset_active':
                    $query->update(['is_active' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه منشی‌های فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedSecretaries = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('secretaries_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'set_active':
                $this->updateActiveStatus(true);
                break;
            case 'unset_active':
                $this->updateActiveStatus(false);
                break;
        }

        $this->groupAction = '';
    }

    private function updateActiveStatus($status)
    {
        $secretaries = Secretary::whereIn('id', $this->selectedSecretaries)->get();
        foreach ($secretaries as $secretary) {
            $secretary->update(['is_active' => $status]);
        }
        $this->selectedSecretaries = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت منشی‌های انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('secretaries_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    private function getSecretariesQuery()
    {
        $query = Secretary::with(['doctor', 'clinic']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhere('mobile', 'like', '%' . $this->search . '%')
                  ->orWhere('national_code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->statusFilter === 'active') {
            $query->where('is_active', true);
        } elseif ($this->statusFilter === 'inactive') {
            $query->where('is_active', false);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $cacheKey = 'secretaries_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage();
        $secretaries = $this->readyToLoad ? Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getSecretariesQuery()->get();
        }) : collect();
        $this->totalFilteredCount = $this->readyToLoad ? $this->getSecretariesQuery()->count() : 0;

        // گروه‌بندی منشی‌ها بر اساس پزشک
        $doctors = $this->readyToLoad ? Doctor::with(['secretaries' => function ($query) {
            if (!empty($this->search) || $this->statusFilter !== '') {
                $query->where(function ($q) {
                    if (!empty($this->search)) {
                        $q->where('first_name', 'like', '%' . $this->search . '%')
                            ->orWhere('last_name', 'like', '%' . $this->search . '%')
                            ->orWhere('mobile', 'like', '%' . $this->search . '%')
                            ->orWhere('national_code', 'like', '%' . $this->search . '%');
                    }
                    if ($this->statusFilter === 'active') {
                        $q->where('is_active', true);
                    } elseif ($this->statusFilter === 'inactive') {
                        $q->where('is_active', false);
                    }
                });
            }
        }])->whereHas('secretaries', function ($query) {
            if (!empty($this->search)) {
                $query->where('first_name', 'like', '%' . $this->search . '%')
                    ->orWhere('last_name', 'like', '%' . $this->search . '%')
                    ->orWhere('mobile', 'like', '%' . $this->search . '%')
                    ->orWhere('national_code', 'like', '%' . $this->search . '%');
            }
            if ($this->statusFilter === 'active') {
                $query->where('is_active', true);
            } elseif ($this->statusFilter === 'inactive') {
                $query->where('is_active', false);
            }
        })->get() : collect();

        return view('livewire.admin.panel.secretaries.secretary-list', [
            'doctors' => $doctors,
            'secretaries' => $secretaries,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
