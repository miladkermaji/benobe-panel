<?php

namespace App\Livewire\Admin\Panel\DoctorDocuments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorDocument;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class DoctorDocumentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = [
        'deleteDoctorDocumentConfirmed' => 'deleteDoctorDocument',
        'deleteSelectedConfirmed' => 'deleteSelected',
        'toggleVerifiedConfirmed' => 'toggleVerifiedConfirmed',
    ];

    public $perPage = 50;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDocuments = [];
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

    public function loadDocuments()
    {
        $this->readyToLoad = true;
    }

    public function confirmToggleVerified($id)
    {
        $document = DoctorDocument::find($id);
        if (!$document) {
            $this->dispatch('show-alert', type: 'error', message: 'مدرک یافت نشد.');
            return;
        }
        $doctorName = $document->doctor->first_name . ' ' . $document->doctor->last_name;
        $action = $document->is_verified ? 'لغو تأیید' : 'تأیید';

        $this->dispatch('confirm-toggle-verified', id: $id, name: $doctorName, action: $action);
    }

    public function toggleVerifiedConfirmed($id)
    {
        $document = DoctorDocument::find($id);
        if (!$document) {
            $this->dispatch('show-alert', type: 'error', message: 'مدرک یافت نشد.');
            return;
        }

        $document->update(['is_verified' => !$document->is_verified]);

        $this->dispatch('show-alert', type: 'success', message: $document->is_verified ? 'مدرک تأیید شد!' : 'تأیید مدرک لغو شد!');
        Cache::forget('doctor_documents_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorDocument($id)
    {
        $document = DoctorDocument::find($id);
        if (!$document) {
            $this->dispatch('show-alert', type: 'error', message: 'مدرک یافت نشد.');
            return;
        }
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        $this->dispatch('show-alert', type: 'success', message: 'مدرک با موفقیت حذف شد!');
        Cache::forget('doctor_documents_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
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
        $currentPageIds = Cache::remember('doctor_documents_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getDocumentsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectedDocuments = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDocuments()
    {
        $currentPageIds = Cache::remember('doctor_documents_ids_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage(), now()->addMinutes(5), function () {
            return $this->getDocumentsQuery()->forPage($this->getPage(), $this->perPage)->pluck('id')->toArray();
        });
        $this->selectAll = !empty($this->selectedDocuments) && count(array_diff($currentPageIds, $this->selectedDocuments)) === 0;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getDocumentsQuery();
            $documents = $query->get();
            foreach ($documents as $document) {
                if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                    Storage::disk('public')->delete($document->file_path);
                }
                $document->delete();
            }
            $this->selectedDocuments = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه مدارک فیلترشده حذف شدند!');
            Cache::forget('doctor_documents_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        if (empty($this->selectedDocuments)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ مدرکی انتخاب نشده است.');
            return;
        }

        $documents = DoctorDocument::whereIn('id', $this->selectedDocuments)->get();
        foreach ($documents as $document) {
            if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
            }
            $document->delete();
        }
        $this->selectedDocuments = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'مدارک انتخاب‌شده حذف شدند!');
        Cache::forget('doctor_documents_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedDocuments) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ مدرکی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getDocumentsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
                case 'verify':
                    $query->update(['is_verified' => true]);
                    $this->dispatch('show-alert', type: 'success', message: 'همه مدارک فیلترشده تأیید شدند!');
                    break;
                case 'unverify':
                    $query->update(['is_verified' => false]);
                    $this->dispatch('show-alert', type: 'success', message: 'تأیید همه مدارک فیلترشده لغو شد!');
                    break;
            }
            $this->selectedDocuments = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            Cache::forget('doctor_documents_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
            case 'verify':
                $this->updateVerification(true);
                break;
            case 'unverify':
                $this->updateVerification(false);
                break;
        }

        $this->groupAction = '';
    }

    private function updateVerification($status)
    {
        $documents = DoctorDocument::whereIn('id', $this->selectedDocuments)->get();
        foreach ($documents as $document) {
            $document->update(['is_verified' => $status]);
        }

        $this->selectedDocuments = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت تأیید مدارک انتخاب‌شده با موفقیت تغییر کرد.');
        Cache::forget('doctor_documents_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage());
    }

    private function getDocumentsQuery()
    {
        $query = DoctorDocument::with('doctor');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhereHas('doctor', function ($q) {
                      $q->where('first_name', 'like', '%' . $this->search . '%')
                        ->orWhere('last_name', 'like', '%' . $this->search . '%')
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                  });
            });
        }

        if ($this->statusFilter === 'verified') {
            $query->where('is_verified', true);
        } elseif ($this->statusFilter === 'unverified') {
            $query->where('is_verified', false);
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $cacheKey = 'doctor_documents_' . $this->search . '_status_' . $this->statusFilter . '_page_' . $this->getPage();
        $documents = $this->readyToLoad ? Cache::remember($cacheKey, now()->addMinutes(5), function () {
            return $this->getDocumentsQuery()->paginate($this->perPage);
        }) : [];
        $this->totalFilteredCount = $this->readyToLoad ? $this->getDocumentsQuery()->count() : 0;

        return view('livewire.admin.panel.doctor-documents.doctor-document-list', [
            'documents' => $documents,
            'totalFilteredCount' => $this->totalFilteredCount,
        ]);
    }
}
