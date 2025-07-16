<?php

namespace App\Livewire\Admin\Panel\Clinics;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use App\Models\Specialty;
use App\Models\Insurance;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class ClinicList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteClinicConfirmed' => 'deleteClinic'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedClinics = [];
    public $selectAll = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadClinics()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteClinic($id)
    {
        $item = MedicalCenter::findOrFail($id);
        // حذف فایل‌های مرتبط
        if ($item->avatar) {
            Storage::disk('public')->delete($item->avatar);
        }
        if ($item->documents) {
            foreach ($item->documents as $document) {
                Storage::disk('public')->delete($document);
            }
        }
        if ($item->galleries) {
            foreach ($item->galleries as $gallery) {
                Storage::disk('public')->delete($gallery['image_path']);
            }
        }
        $item->delete();
        Cache::forget('clinics_' . $this->search . '_page_' . ($this->getPage() ?? 1));
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getClinicsQuery()->pluck('id')->toArray();
        $this->selectedClinics = $value ? $currentPageIds : [];
    }

    public function updatedSelectedClinics()
    {
        $currentPageIds = $this->getClinicsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedClinics) && count(array_diff($currentPageIds, $this->selectedClinics)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedClinics)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کلینیکی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->deleteSelected();
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
        MedicalCenter::whereIn('id', $this->selectedClinics)
            ->update(['is_active' => $status]);

        $this->selectedClinics = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کلینیک‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedClinics)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کلینیکی انتخاب نشده است.');
            return;
        }

        $clinics = MedicalCenter::whereIn('id', $this->selectedClinics)->get();
        foreach ($clinics as $clinic) {
            if ($clinic->avatar) {
                Storage::disk('public')->delete($clinic->avatar);
            }
            if ($clinic->documents) {
                foreach ($clinic->documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
            if ($clinic->galleries) {
                foreach ($clinic->galleries as $gallery) {
                    Storage::disk('public')->delete($gallery['image_path']);
                }
            }
            $clinic->delete();
        }
        Cache::forget('clinics_' . $this->search . '_page_' . ($this->getPage() ?? 1));
        $this->selectedClinics = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک‌های انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();
        Cache::forget('clinics_' . $this->search . '_page_' . ($this->getPage() ?? 1));
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کلینیک با موفقیت تغییر کرد.');
    }

    private function getClinicsQuery()
    {
        return MedicalCenter::where('type', 'clinic')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%');
            })
            ->with(['doctors', 'province', 'city'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getClinicsQuery() : null;
        // بارگذاری تخصص‌ها و بیمه‌ها برای استفاده در قالب
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');

        $services = \App\Models\Service::pluck('name', 'id');

        return view('livewire.admin.panel.clinics.clinic-list', [
            'clinics' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
            'services' => $services,
        ]);
    }
}
