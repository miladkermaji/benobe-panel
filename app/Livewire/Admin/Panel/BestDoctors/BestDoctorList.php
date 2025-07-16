<?php

namespace App\Livewire\Admin\Panel\Bestdoctors;

use App\Models\BestDoctor;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Cache;

class BestDoctorList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteBestDoctorConfirmed' => 'deleteBestDoctor',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleStatusConfirmed' => 'toggleStatusConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedBestDoctors = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadBestDoctors()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleStatus($id)
    {
        $item = BestDoctor::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک یافت نشد.');
            return;
        }
        $doctorName = $item->doctor->first_name . ' ' . $item->doctor->last_name;
        $action = $item->status ? 'غیرفعال کردن' : 'فعال کردن';

        $this->dispatch('confirm-toggle-status', id: $id, name: $doctorName, action: $action);
    }

    public function toggleStatusConfirmed($id)
    {
        $item = BestDoctor::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک یافت نشد.');
            return;
        }

        $item->update(['status' => !$item->status]);

        $this->dispatch('show-alert', type: 'success', message: $item->status ? 'پزشک فعال شد!' : 'پزشک غیرفعال شد!');
        Cache::forget('best_doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteBestDoctor($id)
    {
        $item = BestDoctor::find($id);
        if (!$item) {
            $this->dispatch('show-alert', type: 'error', message: 'پزشک یافت نشد.');
            return;
        }
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'بهترین پزشک حذف شد!');
        Cache::forget('best_doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
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
        $currentPageIds = Cache::remember('best_doctors_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getBestDoctorsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectedBestDoctors = $value ? $currentPageIds : [];
    }

    public function updatedSelectedBestDoctors()
    {
        $currentPageIds = Cache::remember('best_doctors_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getBestDoctorsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectAll = !empty($this->selectedBestDoctors) && count(array_diff($currentPageIds, $this->selectedBestDoctors)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getBestDoctorsQuery();
            $query->delete();
            $this->selectedBestDoctors = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه پزشکان فیلترشده حذف شدند!');
            Cache::forget('best_doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }
        if (empty($this->selectedBestDoctors)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }
        BestDoctor::whereIn('id', $this->selectedBestDoctors)->delete();
        $this->selectedBestDoctors = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'پزشکان انتخاب‌شده حذف شدند!');
        Cache::forget('best_doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedBestDoctors) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ پزشکی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getBestDoctorsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'status_active':
                    $query->update(['status' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه پزشکان فیلترشده فعال شدند!');
                    break;
                case 'status_inactive':
                    $query->update(['status' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه پزشکان فیلترشده غیرفعال شدند!');
                    break;
            }
            $this->selectedBestDoctors = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('best_doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'status_active':
                $this->updateStatus(true);
                break;
            case 'status_inactive':
                $this->updateStatus(false);
                break;
        }

        $this->groupAction = '';
    }

    private function updateStatus($status)
    {
        $items = BestDoctor::whereIn('id', $this->selectedBestDoctors)->get();
        foreach ($items as $item) {
            $item->update(['status' => $status]);
        }

        $this->selectedBestDoctors = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت پزشکان انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('best_doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    private function getBestDoctorsQuery()
    {
        return BestDoctor::query()
            ->with(['doctor', 'clinic'])
            ->when($this->search, function ($query) {
                $search = trim($this->search);
                $query->whereHas('-doctor', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%$search%")
                      ->orWhere('last_name', 'like', "%$search%")
                      ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$search%"]);
                });
            })
            ->when($this->statusFilter, function ($query) {
                if ($this->statusFilter === 'active') {
                    $query->where('status', true);
                } elseif ($this->statusFilter === 'inactive') {
                    $query->where('status', false);
                }
            })
            ->orderBy('id', 'desc');
    }

    public function render()
    {
        $cacheKey = 'best_doctors_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage();
        $bestdoctors = $this->readyToLoad ? Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getBestDoctorsQuery()->paginate($this->perPage);
        }) : [];
        $this->totalFilteredCount = $this->readyToLoad ? $this->getBestDoctorsQuery()->count() : 0;

        return view('livewire.admin.panel.best-doctors.best-doctor-list', [
            'bestdoctors' => $bestdoctors,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
