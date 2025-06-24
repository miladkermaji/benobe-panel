<?php

namespace App\Livewire\Admin\Panel\Laboratories;

use Livewire\Component;
use App\Models\Insurance;
use App\Models\Specialty;
use App\Models\Laboratory;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Storage;

class LaboratoryList extends Component
{
        use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteLaboratoryConfirmed' => 'deleteLaboratory'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedLaboratories = [];
    public $selectAll = false;
    public $groupAction = '';

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadLaboratories()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteLaboratory($id)
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
        $currentPageIds = $this->getLaboratoriesQuery()->pluck('id')->toArray();
        $this->selectedLaboratories = $value ? $currentPageIds : [];
    }

    public function updatedselectedLaboratories()
    {
        $currentPageIds = $this->getLaboratoriesQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedLaboratories) && count(array_diff($currentPageIds, $this->selectedLaboratories)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedLaboratories)) {
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
        MedicalCenter::whereIn('id', $this->selectedLaboratories)
            ->update(['is_active' => $status]);

        $this->selectedLaboratories = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت آزمایشگاه  های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedLaboratories)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ کلینیکی انتخاب نشده است.');
            return;
        }

        $laboratories = MedicalCenter::whereIn('id', $this->selectedLaboratories)->get();
        foreach ($laboratories as $laboratory) {
            if ($laboratory->avatar) {
                Storage::disk('public')->delete($laboratory->avatar);
            }
            if ($laboratory->documents) {
                foreach ($laboratory->documents as $document) {
                    Storage::disk('public')->delete($document);
                }
            }
            if ($laboratory->galleries) {
                foreach ($laboratory->galleries as $gallery) {
                    Storage::disk('public')->delete($gallery['image_path']);
                }
            }
            $laboratory->delete();
        }
        $this->selectedLaboratories = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'آزمایشگاه  های انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->is_active = !$item->is_active;
        $item->save();
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت کلینیک با موفقیت تغییر کرد.');
    }

    private function getLaboratoriesQuery()
    {
        return MedicalCenter::where('type', 'laboratory')
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
        $items = $this->readyToLoad ? $this->getLaboratoriesQuery() : null;
        // بارگذاری تخصص‌ها و بیمه‌ها برای استفاده در قالب
        $specialties = Specialty::pluck('name', 'id');
        $insurances = Insurance::pluck('name', 'id');

        return view('livewire.admin.panel.laboratories.laboratory-list', [
            'laboratories' => $items,
            'specialties' => $specialties,
            'insurances' => $insurances,
        ]);
    }
}
