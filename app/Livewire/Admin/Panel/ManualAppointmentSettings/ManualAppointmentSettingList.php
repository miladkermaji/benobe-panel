<?php

namespace App\Livewire\Admin\Panel\ManualAppointmentSettings;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ManualAppointmentSetting;
use App\Models\Doctor;

class ManualAppointmentSettingList extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['deleteManualAppointmentSettingConfirmed' => 'deleteManualAppointmentSetting'];

    public $perPage = 5;
    public $search = '';
    public $readyToLoad = false; // مقداردهی اولیه مستقیم
    public $expandedDoctors = [];
    public $doctorPages = [];

    protected $queryString = [
        'search' => ['except' => ''],
    ];

   public function mount()
{
    $this->perPage = max($this->perPage, 1);
    $this->readyToLoad = true; // Move initialization here
}

    public function loadManualAppointmentSettings()
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
        $setting = ManualAppointmentSetting::findOrFail($id);
        $setting->update(['is_active' => !$setting->is_active]);
        $this->dispatch('show-alert', type: $setting->is_active ? 'success' : 'info', message: $setting->is_active ? 'تأیید دو مرحله‌ای فعال شد!' : 'تأیید دو مرحله‌ای غیرفعال شد!');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('confirm-delete', id: $id);
    }

    public function deleteManualAppointmentSetting($id)
    {
        $setting = ManualAppointmentSetting::findOrFail($id);
        $setting->delete();
        $this->dispatch('show-alert', type: 'success', message: 'تنظیمات نوبت دستی حذف شد!');
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
            $settings = ManualAppointmentSetting::with('doctor')
                ->whereHas('doctor', function ($q) {
                    $q->whereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $this->search . '%']);
                })
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy('doctor_id');

            foreach ($settings as $doctorId => $doctorSettings) {
                $doctor = Doctor::find($doctorId);
                if (!$doctor) {
                    continue;
                }

                $totalSettings = $doctorSettings->count();
                $currentPage = $this->doctorPages[$doctorId] ?? 1;
                $lastPage = ceil($totalSettings / $this->perPage);
                $currentPage = min(max($currentPage, 1), $lastPage);

                $paginatedSettings = $doctorSettings->forPage($currentPage, $this->perPage)->values();

                $doctorsData[] = [
                    'doctor' => $doctor,
                    'settings' => $paginatedSettings,
                    'totalSettings' => $totalSettings,
                    'currentPage' => $currentPage,
                    'lastPage' => $lastPage,
                ];
            }
        }

        return view('livewire.admin.panel.manual-appointment-settings.manual-appointment-setting-list', [
            'doctors' => $doctorsData,
            'settingsPerPage' => $this->perPage,
        ]);
    }
}
