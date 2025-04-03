<?php

namespace App\Livewire\Admin\Panel\ManualAppointments;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ManualAppointment;
use App\Models\Doctor;
use Morilog\Jalali\Jalalian;

class ManualAppointmentList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteManualAppointmentConfirmed' => 'deleteManualAppointment'];

    public $perPage = 5;
    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = [];
    public $doctorPages = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadManualAppointments()
    {
        $this->readyToLoad = true;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
            $this->doctorPages[$doctorId] = 1;
        }
    }

    public function setDoctorPage($doctorId, $page)
    {
        $this->doctorPages[$doctorId] = max(1, $page);
    }

    public function toggleStatus($id)
    {
        $appointment = ManualAppointment::findOrFail($id);
        $appointment->update(['status' => $appointment->status === 'scheduled' ? 'cancelled' : 'scheduled']);
        $this->dispatch('show-alert', type: $appointment->status === 'scheduled' ? 'success' : 'info', message: $appointment->status === 'scheduled' ? 'نوبت فعال شد!' : 'نوبت لغو شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteManualAppointment($id)
    {
        $appointment = ManualAppointment::findOrFail($id);
        $appointment->delete();
        $this->dispatch('show-alert', type: 'success', message: 'نوبت دستی حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->expandedDoctors = [];
        $this->doctorPages = [];
    }

    public function render()
    {
        $doctorsData = [];
        if ($this->readyToLoad) {
            $appointments = ManualAppointment::with(['doctor', 'user'])
                ->where(function ($query) {
                    $query->whereHas('doctor', function ($q) {
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                    })->orWhereHas('user', function ($q) {
                        $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
                          ->orWhere('national_code', 'like', '%' . $this->search . '%');
                    });
                })
                ->orderBy('appointment_date', 'desc')
                ->get()
                ->groupBy('doctor_id');

            foreach ($appointments as $doctorId => $doctorAppointments) {
                $doctor = Doctor::find($doctorId);
                if (!$doctor) {
                    continue;
                }

                $totalAppointments = $doctorAppointments->count();
                $currentPage = $this->doctorPages[$doctorId] ?? 1;
                $lastPage = ceil($totalAppointments / $this->perPage);
                $currentPage = min(max($currentPage, 1), $lastPage);

                $paginatedAppointments = $doctorAppointments->forPage($currentPage, $this->perPage)->values();

                $doctorsData[] = [
                    'doctor' => $doctor,
                    'appointments' => $paginatedAppointments,
                    'totalAppointments' => $totalAppointments,
                    'currentPage' => $currentPage,
                    'lastPage' => $lastPage,
                ];
            }
        }

        return view('livewire.admin.panel.manual-appointments.manual-appointment-list', [
            'doctors' => $doctorsData,
            'appointmentsPerPage' => $this->perPage,
        ]);
    }
}
