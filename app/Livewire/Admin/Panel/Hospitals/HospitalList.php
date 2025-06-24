<?php

namespace App\Livewire\Admin\Panel\Hospitals;

use Livewire\Component;
use App\Models\Hospital;
use App\Models\Insurance;
use App\Models\Specialty;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Storage;

class HospitalList extends Component
{
      use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteHospitalConfirmed' => 'deleteHospital'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedHospitals = [];
    public $selectAll = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadHospitals()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteHospital($id)
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
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getHospitalsQuery()->pluck('id')->toArray();
        $this->selectedHospitals = $value ? $currentPageIds : [];
    }

    public function updatedselectedHospitals()
    {
        $currentPageIds = $this->getHospitalsQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedHospitals) && count(array_diff($currentPageIds, $this->selectedHospitals)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedHospitals)) {
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
        MedicalCenter::whereIn('id', $this->selectedHospitals)
            ->update(['is_active' => $status]);

        $this->selectedHospitals = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت بیمارستانهای انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedHospitals)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کلینیکی انتخاب نشده است.');
            return;
        }

        $hospitals = MedicalCenter::whereIn('id', $this->selectedHospitals)->get();
        foreach ($hospitals as $hospital) {
            if ($hospital->avatar) {
                Storage::disk('public')->delete($hospital->avatar);
            }
            if ($hospital->documents) {
                foreach ($hospital->documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
            if ($hospital->galleries) {
                foreach ($hospital->galleries as $gallery) {
                    Storage::disk('public')->delete($gallery['image_path']);
                }
            }
            $hospital->delete();
        }
        $this->selectedHospitals = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'بیمارستانهای انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کلینیک با موفقیت تغییر کرد.');
    }

    private function getHospitalsQuery()
    {
        return MedicalCenter::where('type', 'hospital')
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
        $items = $this->readyToLoad ? $this->getHospitalsQuery() : null;
        // بارگذاری تخصص‌ها و بیمه‌ها برای استفاده در قالب
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');

        return view('livewire.admin.panel.hospitals.hospital-list', [
            'hospitals' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
        ]);
    }
}
