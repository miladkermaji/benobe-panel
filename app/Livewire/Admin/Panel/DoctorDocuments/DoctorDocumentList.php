<?php

namespace App\Livewire\Admin\Panel\DoctorDocuments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorDocument;
use App\Models\Doctor;
use Illuminate\Support\Facades\Storage;

class DoctorDocumentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorDocumentConfirmed' => 'deleteDoctorDocument'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDocuments = [];
    public $selectAll = [];
    public $expandedDoctors = [];
    public $viewMode = 'table'; // 'table' or 'card'

    protected $queryString = [
        'search' => ['except' => ''],
        'viewMode' => ['except' => 'table'],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDocuments()
    {
        $this->readyToLoad = true;
    }

    public function toggleVerified($id)
    {
        $document = DoctorDocument::findOrFail($id);
        $document->update(['is_verified' => !$document->is_verified]);
        $this->dispatch('show-alert', type: $document->is_verified ? 'success' : 'info', message: $document->is_verified ? 'مدرک تأیید شد!' : 'تأیید مدرک لغو شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorDocument($id)
    {
        $document = DoctorDocument::findOrFail($id);
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        $document->delete();
        $this->dispatch('show-alert', type: 'success', message: 'مدرک با موفقیت حذف شد!');
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value, $doctorId)
    {
        $documentIds = $this->getDocumentsQuery($doctorId)->pluck('id')->toArray();

        if ($value) {
            // وقتی تیک می‌خوره، همه مدارک اون پزشک رو اضافه کن
            $this->selectedDocuments = array_unique(array_merge($this->selectedDocuments, $documentIds));
        } else {
            // وقتی تیک برداشته می‌شه، همه مدارک اون پزشک رو حذف کن
            $this->selectedDocuments = array_diff($this->selectedDocuments, $documentIds);
        }
    }

    public function updatedSelectedDocuments()
    {
        // چک کن که آیا همه مدارک هر پزشک انتخاب شدن یا نه
        foreach ($this->getDoctors() as $doctor) {
            $doctorId = $doctor->id;
            $documentIds = $this->getDocumentsQuery($doctorId)->pluck('id')->toArray();
            $this->selectAll[$doctorId] = !empty($documentIds) && empty(array_diff($documentIds, $this->selectedDocuments));
        }
    }

    public function deleteSelected()
    {
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
        $this->selectAll = [];
        $this->dispatch('show-alert', type: 'success', message: 'مدارک انتخاب‌شده حذف شدند!');
    }

    public function toggleViewMode()
    {
        $this->viewMode = $this->viewMode === 'table' ? 'card' : 'table';
    }

    private function getDoctors()
    {
        $query = Doctor::with('documents');

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('first_name', 'like', '%' . $this->search . '%')
                  ->orWhere('last_name', 'like', '%' . $this->search . '%')
                  ->orWhereHas('documents', function ($q) {
                      $q->where('title', 'like', '%' . $this->search . '%');
                  });
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    private function getDocumentsQuery($doctorId)
    {
        $query = DoctorDocument::where('doctor_id', $doctorId);

        if (!empty($this->search)) {
            $query->where('title', 'like', '%' . $this->search . '%');
        }

        return $query->orderBy('created_at', 'desc');
    }

    public function render()
    {
        $doctors = $this->readyToLoad ? $this->getDoctors() : [];

        return view('livewire.admin.panel.doctor-documents.doctor-document-list', [
            'doctors' => $doctors,
        ]);
    }
}
