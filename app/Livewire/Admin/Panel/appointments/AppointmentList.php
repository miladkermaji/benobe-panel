<?php

namespace App\Livewire\Admin\Panel\Appointments;

use Livewire\Component;
use App\Models\Appointment;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class AppointmentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteAppointmentConfirmed' => 'deleteAppointment'];

    public $perPage = 100;
    public $appointmentsPerPage = 100;
    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = [];
    public $doctorPages = [];
    public $selectedAppointments = [];
    public $selectAll = false;
    public $groupAction = '';
    public $applyToAllFiltered = false;
    public $totalFilteredCount = 0;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
        $this->appointmentsPerPage = max($this->appointmentsPerPage, 1);
    }

    public function loadAppointments()
    {
        $this->readyToLoad = true;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
            if (!isset($this->doctorPages[$doctorId])) {
                $this->doctorPages[$doctorId] = 1;
            }
        }
    }

    public function setDoctorPage($doctorId, $page)
    {
        $this->doctorPages[$doctorId] = max(1, $page);
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteAppointment($id)
    {
        $appointment = Appointment::findOrFail($id);
        $appointment->delete();
        $this->dispatch('show-alert', type: 'success', message: 'نوبت با موفقیت حذف شد.');
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->doctorPages = [];
    }

    public function updatedSelectAll($value)
    {
        $currentPageIds = $this->getCurrentPageAppointmentIds();
        $this->selectedAppointments = $value ? $currentPageIds : [];
    }

    public function updatedSelectedAppointments()
    {
        $currentPageIds = $this->getCurrentPageAppointmentIds();
        $this->selectAll = !empty($this->selectedAppointments) && count(array_diff($currentPageIds, $this->selectedAppointments)) === 0;
    }

    private function getCurrentPageAppointmentIds()
    {
        $ids = [];
        foreach ($this->doctors as $doctor) {
            foreach ($doctor['appointments'] as $appointment) {
                $ids[] = $appointment->id;
            }
        }
        return $ids;
    }

    public function deleteSelected($allFiltered = null)
    {
        if ($allFiltered === 'allFiltered') {
            $query = $this->getAppointmentsQuery();
            $appointments = $query->get();
            foreach ($appointments as $appointment) {
                $appointment->delete();
            }
            $this->selectedAppointments = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            $this->dispatch('show-alert', type: 'success', message: 'همه نوبت‌های فیلترشده حذف شدند!');
            return;
        }

        if (empty($this->selectedAppointments)) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نوبتی انتخاب نشده است.');
            return;
        }

        $appointments = Appointment::whereIn('id', $this->selectedAppointments)->get();
        foreach ($appointments as $appointment) {
            $appointment->delete();
        }
        $this->selectedAppointments = [];
        $this->selectAll = false;
        $this->dispatch('show-alert', type: 'success', message: 'نوبت‌های انتخاب‌شده حذف شدند!');
    }

    public function executeGroupAction()
    {
        if (empty($this->selectedAppointments) && !$this->applyToAllFiltered) {
            $this->dispatch('show-alert', type: 'warning', message: 'هیچ نوبتی انتخاب نشده است.');
            return;
        }

        if (empty($this->groupAction)) {
            $this->dispatch('show-alert', type: 'warning', message: 'لطفا یک عملیات را انتخاب کنید.');
            return;
        }

        if ($this->applyToAllFiltered) {
            $query = $this->getAppointmentsQuery();
            switch ($this->groupAction) {
                case 'delete':
                    $this->dispatch('confirm-delete-selected', ['allFiltered' => true]);
                    return;
            }
            $this->selectedAppointments = [];
            $this->selectAll = false;
            $this->applyToAllFiltered = false;
            $this->groupAction = '';
            $this->resetPage();
            return;
        }

        switch ($this->groupAction) {
            case 'delete':
                $this->dispatch('confirm-delete-selected', ['allFiltered' => false]);
                break;
        }

        $this->groupAction = '';
    }

    private function getAppointmentsQuery()
    {
        return Appointment::with('doctor', 'patientable')
            ->where(function ($query) {
                $query->whereHas('doctor', function ($q) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
                      ->orWhere('national_code', 'like', '%' . $this->search . '%');
                })->orWhereHas('patientable', function ($q) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
                      ->orWhere('national_code', 'like', '%' . $this->search . '%');
                })->orWhere('tracking_code', 'like', '%' . $this->search . '%');
            })
            ->orderBy('appointment_date', 'desc');
    }

    public function render()
    {
        $doctors = $this->readyToLoad ? $this->getAppointmentsQuery()
            ->get()
            ->groupBy('doctor_id')
            ->map(function ($appointments, $doctorId) {
                $currentPage = $this->doctorPages[$doctorId] ?? 1;
                $paginatedAppointments = $appointments->forPage($currentPage, $this->appointmentsPerPage);
                return [
                    'doctor' => $appointments->first()->doctor,
                    'appointments' => $paginatedAppointments->values(),
                    'totalAppointments' => $appointments->count(),
                    'currentPage' => $currentPage,
                    'lastPage' => ceil($appointments->count() / $this->appointmentsPerPage),
                ];
            }) : [];
        $this->totalFilteredCount = $this->readyToLoad ? $this->getAppointmentsQuery()->count() : 0;

        return view('livewire.admin.panel.appointments.appointment-list', [
            'doctors' => $doctors,
        ]);
    }
}
