<?php

namespace App\Livewire\Mc\Panel\Doctornotes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorNote;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use App\Traits\HasSelectedDoctor;

class DoctorNoteList extends Component
{
    use WithPagination;
    use HasSelectedDoctor;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorNoteConfirmed' => 'deleteDoctorNote'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorNotes = [];
    public $selectAll = false;
    public $groupAction = '';
    public $refreshKey = 0;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDoctorNotes()
    {
        $this->readyToLoad = true;
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorNote($id)
    {
        $query = DoctorNote::where('id', $id);

        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            $query->where('doctor_id', $selectedDoctorId)
                  ->where('medical_center_id', $medicalCenter->id);
        } else {
            // Handle doctor/secretary authentication
            $query->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id);
        }

        $item = $query->firstOrFail();
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'یادداشت پزشک حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getDoctorNotesQuery()->pluck('id')->toArray();
        $this->selectedDoctorNotes = $value ? $currentPageIds : [];
    }

    public function updatedSelectedDoctorNotes()
    {
        $currentPageIds = $this->getDoctorNotesQuery()->pluck('id')->toArray();
        $this->selectAll = !empty($this->selectedDoctorNotes) && count(array_diff($currentPageIds, $this->selectedDoctorNotes)) === 0;
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedDoctorNotes)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ یادداشتی انتخاب نشده است.');
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
        $query = DoctorNote::whereIn('id', $this->selectedDoctorNotes);

        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            $query->where('doctor_id', $selectedDoctorId)
                  ->where('medical_center_id', $medicalCenter->id);
        } else {
            // Handle doctor/secretary authentication
            $query->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id);
        }

        $query->update(['status' => $status]);

        $this->selectedDoctorNotes = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'وضعیت یادداشت‌های انتخاب‌شده با موفقیت تغییر کرد.');
    }

    public function deleteSelected()
    {
        if (empty($this->selectedDoctorNotes)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ یادداشتی انتخاب نشده است.');
            return;
        }

        $query = DoctorNote::whereIn('id', $this->selectedDoctorNotes);

        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            $query->where('doctor_id', $selectedDoctorId)
                  ->where('medical_center_id', $medicalCenter->id);
        } else {
            // Handle doctor/secretary authentication
            $query->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id);
        }

        $query->delete();
        $this->selectedDoctorNotes = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'یادداشت‌های انتخاب‌شده حذف شدند!');
    }

    public function toggleStatus($id)
    {
        $query = DoctorNote::where('id', $id);

        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            if (!$selectedDoctorId) {
                $this->dispatch('show-alert', type: 'error', message: 'هیچ پزشکی انتخاب نشده است.');
                return;
            }

            $query->where('doctor_id', $selectedDoctorId)
                  ->where('medical_center_id', $medicalCenter->id);
        } else {
            // Handle doctor/secretary authentication
            $query->where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id);
        }

        $note = $query->firstOrFail();
        $note->status = $note->status === 'active' ? 'inactive' : 'active';
        $note->save();

        $this->dispatch('show-alert', type: 'success', message: 'وضعیت یادداشت با موفقیت تغییر کرد.');
    }

    private function getDoctorNotesQuery()
    {
        // Handle medical center authentication
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $this->getSelectedDoctorId();

            if (!$selectedDoctorId) {
                // If no doctor is selected, return empty result
                return DoctorNote::where('id', 0)->paginate($this->perPage);
            }

            return DoctorNote::where('doctor_id', $selectedDoctorId)
                ->where('medical_center_id', $medicalCenter->id)
                ->where(function ($query) {
                    $query->where('notes', 'like', '%' . $this->search . '%')
                        ->orWhere('appointment_type', 'like', '%' . $this->search . '%');
                })
                ->with(['medicalCenter'])
                ->paginate($this->perPage);
        }

        // Handle doctor/secretary authentication (existing logic)
        return DoctorNote::where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->where(function ($query) {
                $query->where('notes', 'like', '%' . $this->search . '%')
                    ->orWhere('appointment_type', 'like', '%' . $this->search . '%');
            })
            ->with(['medicalCenter'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getDoctorNotesQuery() : null;

        return view('livewire.mc.panel.doctor-notes.doctor-note-list', [
            'doctorNotes' => $items,
        ]);
    }

    #[On('doctorSelected')]
    public function handleDoctorSelected($data)
    {
        // Refresh the component when a new doctor is selected
        $this->readyToLoad = false;
        $this->selectedDoctorNotes = [];
        $this->selectAll = false;
        $this->search = '';
        $this->resetPage();
        $this->refreshKey++; // Increment refreshKey to force re-render

        // Force a fresh load of doctor notes
        $this->loadDoctorNotes();

        // Dispatch an event to show that the data has been refreshed
        $this->dispatch('doctor-notes-refreshed');
    }
}
