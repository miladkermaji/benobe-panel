<?php

namespace App\Livewire\Admin\Panel\DoctorHolidays;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\DoctorHoliday;
use App\Models\Doctor;
use Illuminate\Pagination\LengthAwarePaginator;
use Morilog\Jalali\Jalalian;

class DoctorHolidayList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';
    protected $listeners = ['deleteDoctorHolidayConfirmed' => 'deleteDoctorHoliday'];

    public $perPage = 10;
    public $search = '';
    public $readyToLoad = false;
    public $expandedDoctors = [];
    public $holidayPerPage = 5;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        $this->perPage = max($this->perPage, 1);
    }

    public function loadDoctorHolidays()
    {
        $this->readyToLoad = true;
    }

    public function toggleDoctor($doctorId)
    {
        if (in_array($doctorId, $this->expandedDoctors)) {
            $this->expandedDoctors = array_diff($this->expandedDoctors, [$doctorId]);
        } else {
            $this->expandedDoctors[] = $doctorId;
        }
    }

    public function toggleStatus($id)
    {
        $item = DoctorHoliday::findOrFail($id);
        $newStatus = $item->status === 'active' ? 'inactive' : 'active';
        $item->update(['status' => $newStatus]);

        $this->dispatch(
            'show-alert',
            type: $newStatus === 'active' ? 'success' : 'info',
            message: $newStatus === 'active' ? 'تعطیلات فعال شد!' : 'تعطیلات غیرفعال شد!'
        );
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteDoctorHoliday($id)
    {
        $item = DoctorHoliday::findOrFail($id);
        $item->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تعطیلات پزشک حذف شد!');
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->expandedDoctors = [];
    }

    private function getDoctorsQuery()
    {
        return Doctor::whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%'])
            ->with(['doctorHolidays' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->paginate($this->perPage);
    }

    public function getPaginatedHolidays($doctorId, $page = 1)
    {
        $holidays = DoctorHoliday::where('doctor_id', $doctorId)
            ->with('clinic')
            ->get();

        $dates = [];
        foreach ($holidays as $holiday) {
            $holidayDates = $holiday->holiday_dates ?? []; // مطمئن می‌شیم آرایه باشه
            if (!is_array($holidayDates)) {
                $holidayDates = json_decode($holidayDates ?? '[]', true); // اگه JSON باشه، به آرایه تبدیل می‌شه
            }

            foreach ($holidayDates as $date) {
                $dates[] = [
                    'id' => $holiday->id,
                    'date' => Jalalian::fromDateTime($date)->format('Y/m/d'),
                    'status' => $holiday->status,
                    'clinic' => $holiday->clinic ? $holiday->clinic->name : null,
                ];
            }
        }

        $total = count($dates);
        $perPage = $this->holidayPerPage;
        $offset = ($page - 1) * $perPage;
        $paginatedDates = array_slice($dates, $offset, $perPage);

        return new LengthAwarePaginator($paginatedDates, $total, $perPage, $page, [
            'path' => url()->current(),
            'query' => ['doctor_id' => $doctorId],
        ]);
    }

    public function render()
    {
        $doctors = $this->readyToLoad ? $this->getDoctorsQuery() : null;

        return view('livewire.admin.panel.doctor-holidays.doctor-holiday-list', [
            'doctors' => $doctors,
        ]);
    }
}
