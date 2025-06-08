<?php

namespace App\Livewire\Dr\Panel\Doctornotes;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorNote;
use Illuminate\Support\Facades\Auth;

class DoctorNoteList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteDoctorNoteConfirmed' => 'deleteDoctorNote'];

    public $perPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $selectedDoctorNotes = [];
    public $selectAll = false;
    public $groupAction = '';

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
        $item = DoctorNote::findOrFail($id);
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
        DoctorNote::whereIn('id', $this->selectedDoctorNotes)
            ->update(['status' => $status]);

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

        DoctorNote::whereIn('id', $this->selectedDoctorNotes)->delete();
        $this->selectedDoctorNotes = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'یادداشت‌های انتخاب‌شده حذف شدند!');
    }

    private function getDoctorNotesQuery()
    {
        return DoctorNote::where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)
            ->where(function ($query) {
                $query->where('notes', 'like', '%' . $this->search . '%')
                    ->orWhere('appointment_type', 'like', '%' . $this->search . '%');
            })
            ->with(['clinic'])
            ->paginate($this->perPage);
    }

    public function render()
    {
        $items = $this->readyToLoad ? $this->getDoctorNotesQuery() : null;

        return view('livewire.dr.panel.doctor-notes.doctor-note-list', [
            'doctorNotes' => $items,
        ]);
    }
}
