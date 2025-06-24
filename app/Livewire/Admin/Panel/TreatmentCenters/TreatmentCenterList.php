<?php

namespace App\Livewire\Admin\Panel\TreatmentCenters;

use Livewire\Component;
use App\Models\Insurance;
use App\Models\Specialty;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use App\Models\TreatmentCenter;
use Illuminate\Support\Facades\Storage;

class TreatmentCenterList extends Component
{
     use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteTreatmentCenterConfirmed' => 'deleteTreatmentCenter'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedTreatmentCenters = [];
    public $selectAll = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadTreatmentCenters()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteTreatmentCenter($id)
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
        $currentPageIds = $this->getTreatmentCentersQuery()->pluck('id')->toArray();
        $this->selectedTreatmentCenters = $value ? $currentPageIds : [];
    }

    public function updatedselectedTreatmentCenters()
    {
        $currentPageIds = $this->getTreatmentCentersQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedTreatmentCenters) && count(array_diff($currentPageIds, $this->selectedTreatmentCenters)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedTreatmentCenters)) {
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
        MedicalCenter::whereIn('id', $this->selectedTreatmentCenters)
            ->update(['is_active' => $status]);

        $this->selectedTreatmentCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت درمانگاه  های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedTreatmentCenters)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کلینیکی انتخاب نشده است.');
            return;
        }

        $treatmentCenters = MedicalCenter::whereIn('id', $this->selectedTreatmentCenters)->get();
        foreach ($treatmentCenters as $treatmentCenter) {
            if ($treatmentCenter->avatar) {
                Storage::disk('public')->delete($treatmentCenter->avatar);
            }
            if ($treatmentCenter->documents) {
                foreach ($treatmentCenter->documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
            if ($treatmentCenter->galleries) {
                foreach ($treatmentCenter->galleries as $gallery) {
                    Storage::disk('public')->delete($gallery['image_path']);
                }
            }
            $treatmentCenter->delete();
        }
        $this->selectedTreatmentCenters = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'درمانگاه  های انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کلینیک با موفقیت تغییر کرد.');
    }

    private function getTreatmentCentersQuery()
    {
        return MedicalCenter::where('type', 'treatmentCenter')
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
        $items = $this->readyToLoad ? $this->getTreatmentCentersQuery() : null;
        // بارگذاری تخصص‌ها و بیمه‌ها برای استفاده در قالب
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');

        return view('livewire.admin.panel.treatment-centers.treatment-centers-list', [
            'treatmentCenters' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
        ]);
    }
}
