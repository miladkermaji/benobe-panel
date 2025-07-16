<?php

namespace App\Livewire\Admin\Panel\DoctorSpecialties;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorSpecialty;
use App\Models\Doctor;
use Illuminate\Support\Facades\Cache;

class DoctorSpecialtyList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteSpecialtyConfirmed' => 'deleteSpecialty',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleMainConfirmed' => 'toggleMainConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedSpecialties = [];
    public $selectAll = false;
    public $groupAction = '';
    public $statusFilter = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;
    public $openDoctors = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadSpecialties()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleMain($id)
    {
        $specialty = DoctorSpecialty::find($id);
        if (!$specialty) {
            $this->dispatch('show-alert', type: 'error', message: 'تخصص یافت نشد.');
            return;
        }
        $doctorName = $specialty->doctor->first_name . ' ' . $specialty->doctor->last_name;
        $action = $specialty->is_main ? 'لغو تخصص اصلی' : 'تنظیم به عنوان تخصص اصلی';

        $this->dispatch('confirm-toggle-main', id: $id, name: $doctorName, action: $action);
    }

    public function toggleMainConfirmed($id)
    {
        $specialty = DoctorSpecialty::find($id);
        if (!$specialty) {
            $this->dispatch('show-alert', type: 'error', message: 'تخصص یافت نشد.');
            return;
        }

        $specialty->update(['is_main' => !$specialty->is_main]);

        $this->dispatch('show-alert', type: 'success', message: $specialty->is_main ? 'تخصص به عنوان اصلی تنظیم شد!' : 'تخصص اصلی لغو شد!');
        Cache::forget('doctor_specialties_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteSpecialty($id)
    {
        $specialty = DoctorSpecialty::find($id);
        if (!$specialty) {
            $this->dispatch('show-alert', type: 'error', message: 'تخصص یافت نشد.');
            return;
        }
        $specialty->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تخصص با موفقیت حذف شد!');
        Cache::forget('doctor_specialties_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
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
        $currentPageIds = Cache::remember('doctor_specialties_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getSpecialtiesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectedSpecialties = $value ? $currentPageIds : [];
    }

    public function updatedSelectedSpecialties()
    {
        $currentPageIds = Cache::remember('doctor_specialties_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getSpecialtiesQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectAll = !empty($this->selectedSpecialties) && count(array_diff($currentPageIds, $this->selectedSpecialties)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getSpecialtiesQuery();
            $specialties = $query->get();
            foreach ($specialties as $specialty) {
                $specialty->delete();
            }
            $this->selectedSpecialties = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه تخصص‌های فیلترشده حذف شدند!');
            Cache::forget('doctor_specialties_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        if (empty($this->selectedSpecialties)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تخصصی انتخاب نشده است.');
            return;
        }

        $specialties = DoctorSpecialty::whereIn('id', $this->selectedSpecialties)->get();
        foreach ($specialties as $specialty) {
            $specialty->delete();
        }
        $this->selectedSpecialties = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'تخصص‌های انتخاب‌شده حذف شدند!');
        Cache::forget('doctor_specialties_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedSpecialties) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ تخصصی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getSpecialtiesQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'set_main':
                    $query->update(['is_main' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه تخصص‌های فیلترشده به عنوان اصلی تنظیم شدند!');
                    break;
                case 'unset_main':
                    $query->update(['is_main' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'تخصص اصلی همه تخصص‌های فیلترشده لغو شد!');
                    break;
            }
            $this->selectedSpecialties = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('doctor_specialties_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'set_main':
                $this->updateMainStatus(true);
                break;
            case 'unset_main':
                $this->updateMainStatus(false);
                break;
        }

        $this->groupAction = '';
    }

    private function updateMainStatus($status)
    {
        $specialties = DoctorSpecialty::whereIn('id', $this->selectedSpecialties)->get();
        foreach ($specialties as $specialty) {
            $specialty->update(['is_main' => $status]);
        }

        $this->selectedSpecialties = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت تخصص اصلی انتخاب‌شده‌ها با موفقیت تغییر کرد.');
        Cache::forget('doctor_specialties_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function toggleDoctorRow($doctorId)
    {
        if (in_array($doctorId, $this->openDoctors)) {
            $this->openDoctors = array_diff($this->openDoctors, [$doctorId]);
        } else {
            $this->openDoctors[] = $doctorId;
        }
    }

    private function getSpecialtiesQuery()
    {
        $query = DoctorSpecialty::with(['doctor', 'specialty', 'academicDegree']);

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('specialty_title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('specialty', function ($q) {
                      $q->where('name', 'like', '%' . $this->search . '%');
                  })
                  ->orWhereHas('doctor', function ($q) {
                      $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                  });
            });
        }

        if ($this->statusFilter === 'main') {
            $query->where('is_main', true);
        } elseif ($this->statusFilter === 'not_main') {
            $query->where('is_main', false);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $cacheKey = 'doctor_specialties_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage();
        $specialties = $this->readyToLoad ? Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getSpecialtiesQuery()->paginate($this->perPage);
        }) : [];
        $this->totalFilteredCount = $this->readyToLoad ? $this->getSpecialtiesQuery()->count() : 0;

        return view('livewire.admin.panel.doctor-specialties.doctor-specialty-list', [
            'specialties' => $specialties,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
