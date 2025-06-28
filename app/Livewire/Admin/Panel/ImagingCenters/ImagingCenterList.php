<?php

namespace App\Livewire\Admin\Panel\ImagingCenters;

use Livewire\Component;
use App\Models\Insurance;
use App\Models\Specialty;
use Livewire\WithPagination;
use App\Models\ImagingCenter;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Storage;

class ImagingCenterList extends Component
{
   use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteImagingCenterConfirmed' => 'deleteImagingCenter'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedImagingCenters = [];
    public $selectAll = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadimagingCenters()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteImagingCenter($id)
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
        $currentPageIds = $this->getImagingCentersQuery()->pluck('id')->toArray();
        $this->selectedImagingCenters = $value ? $currentPageIds : [];
    }

    public function updatedselectedImagingCenters()
    {
        $currentPageIds = $this->getImagingCentersQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedImagingCenters) && count(array_diff($currentPageIds, $this->selectedImagingCenters)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedImagingCenters)) {
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
        MedicalCenter::whereIn('id', $this->selectedImagingCenters)
            ->update(['is_active' => $status]);

        $this->selectedImagingCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت مراکز تصویربرداری  های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedImagingCenters)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کلینیکی انتخاب نشده است.');
            return;
        }

        $imagingCenters = MedicalCenter::whereIn('id', $this->selectedImagingCenters)->get();
        foreach ($imagingCenters as $imagingCenter) {
            if ($imagingCenter->avatar) {
                Storage::disk('public')->delete($imagingCenter->avatar);
            }
            if ($imagingCenter->documents) {
                foreach ($imagingCenter->documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
            if ($imagingCenter->galleries) {
                foreach ($imagingCenter->galleries as $gallery) {
                    Storage::disk('public')->delete($gallery['image_path']);
                }
            }
            $imagingCenter->delete();
        }
        $this->selectedImagingCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'مراکز تصویربرداری  های انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کلینیک با موفقیت تغییر کرد.');
    }

    private function getImagingCentersQuery()
    {
        return MedicalCenter::where('type', 'imagingCenter')
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
        $items = $this->readyToLoad ? $this->getImagingCentersQuery() : null;
        // بارگذاری تخصص‌ها و بیمه‌ها برای استفاده در قالب
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');

$services = \App\Models\Service::pluck('name', 'id');

        return view('livewire.admin.panel.imaging-centers.imaging-center-list', [
            'imagingCenters' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
            'services' => $services,
        ]);
    }
}
