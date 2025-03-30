<?php

namespace App\Livewire;

use App\Models\Admin\Manager;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class AdminDashboard extends Component
{
    public $totalDoctors      = 0;
    public $totalPatients     = 0;
    public $totalSecretaries  = 0;
    public $totalManagers     = 0;
    public $totalClinics      = 0;
    public $totalAppointments = 0;

    public $appointmentsByMonth;
    public $doctorSpecialties;
    public $appointmentStatuses;
    public $appointmentsByDayOfWeek;
    public $clinicActivity;
    public $clinicActivityLabels;

    public function mount()
    {
        $this->loadStatistics();
        $this->loadChartData();
    }

    private function loadStatistics()
    {
        try {
            $this->totalDoctors      = Doctor::whereNull('deleted_at')->count();
            $this->totalPatients     = User::where('user_type', 0)->whereNull('deleted_at')->count();
            $this->totalSecretaries  = Secretary::whereNull('deleted_at')->count();
            $this->totalManagers     = Manager::whereNull('deleted_at')->count();
            $this->totalClinics      = Clinic::count();
            $this->totalAppointments = Appointment::whereNull('deleted_at')->count();
        } catch (\Exception $e) {
            Log::error('Error loading dashboard statistics: ' . $e->getMessage());
        }
    }

    private function loadChartData()
    {
        $this->appointmentsByMonth = Appointment::selectRaw('MONTH(appointment_date) as month, COUNT(*) as count')
            ->whereNull('appointments.deleted_at')
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $this->doctorSpecialties = Doctor::selectRaw('specialty_id, COUNT(*) as count')
            ->whereNull('deleted_at')
            ->groupBy('specialty_id')
            ->pluck('count', 'specialty_id')
            ->toArray();

        $this->appointmentStatuses = Appointment::selectRaw('status, COUNT(*) as count')
            ->whereNull('appointments.deleted_at')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->appointmentsByDayOfWeek = Appointment::selectRaw('WEEKDAY(appointment_date) as day, COUNT(*) as count')
            ->whereNull('appointments.deleted_at')
            ->groupBy('day')
            ->pluck('count', 'day')
            ->toArray();

        $this->clinicActivity = Appointment::selectRaw('clinic_id, COUNT(*) as count')
            ->whereNull('appointments.deleted_at')
            ->groupBy('clinic_id')
            ->pluck('count', 'clinic_id')
            ->toArray();

        $this->clinicActivityLabels = collect(array_keys($this->clinicActivity))
            ->map(fn ($id) => 'کلینیک ' . $id)
            ->all();
    }

    public function render()
    {
        return view('livewire.admin-dashboard');
    }
}
