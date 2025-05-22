<?php

namespace App\Http\Controllers\Admin\Panel\Dashboard;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Appointment;
use App\Models\Admin\Manager;
use App\Livewire\AdminDashboard;
use App\Http\Controllers\Admin\Controller;

class AdminDashboardController extends Controller
{
    public function index()
    {
        return view('admin.panel.dashboard.index', [
            'totalDoctors' => Doctor::whereNull('deleted_at')->count(),
            'totalPatients' => User::where('user_type', 0)->whereNull('deleted_at')->count(),
            'totalSecretaries' => Secretary::whereNull('deleted_at')->count(),
            'totalManagers' => Manager::whereNull('deleted_at')->count(),
            'totalClinics' => Clinic::count(),
            'totalAppointments' => Appointment::whereNull('deleted_at')->count(),
            'appointmentsByMonth' => Appointment::selectRaw('MONTH(appointment_date) as month, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray(),
            'appointmentStatuses' => Appointment::selectRaw('status, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
            'appointmentsByDayOfWeek' => Appointment::selectRaw('WEEKDAY(appointment_date) as day, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('day')
                ->pluck('count', 'day')
                ->toArray(),
            'clinicActivity' => Appointment::selectRaw('clinic_id, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('clinic_id')
                ->pluck('count', 'clinic_id')
                ->toArray(),
            'clinicActivityLabels' => collect(Appointment::select('clinic_id')
                ->whereNull('appointments.deleted_at')
                ->distinct()
                ->pluck('clinic_id'))
                ->map(fn ($id) => 'کلینیک ' . $id)
                ->all()
        ]);
    }
}
