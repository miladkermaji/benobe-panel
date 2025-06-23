<?php

namespace App\Livewire\Admin\Panel\Clinics;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MedicalCenter;
use Illuminate\Support\Facades\Storage;

class ClinicList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteClinicConfirmed' => 'deleteClinic'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $selectedClinics = [];
    public $selectAll = false;

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

    public function toggleStatus($id)
    {
        $item = MedicalCenter::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
        $this->dispatch('show-alert', type: $item->is_active ? 'success' : 'info', message: $item->is_active ? 'فعال شد!' : 'غیرفعال شد!');
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
        $this->selectedClinics = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'کلینیک‌های انتخاب‌شده حذف شدند!');
    }

    private function getClinicsQuery()
    {
        return MedicalCenter::where('type', 'clinic')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%')
                    ->orWhere('title', 'like', '%' . $this->search . '%');
            })
            ->with(['doctor', 'province', 'city'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getClinicsQuery() : null;
        return view('livewire.admin.panel.clinics.clinic-list', [
            'clinics' => $items,
        ]);
    }
}
