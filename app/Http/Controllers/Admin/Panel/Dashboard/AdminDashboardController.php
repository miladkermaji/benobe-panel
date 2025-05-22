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
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // ترجمه وضعیت‌های نوبت به فارسی
        $statusTranslations = [
            'scheduled' => 'رزرو شده',
            'cancelled' => 'لغو شده',
            'attended' => 'حاضر شده',
            'missed' => 'غایب',
            'pending_review' => 'در انتظار بررسی'
        ];

        // ترجمه نوع نوبت به فارسی
        $appointmentTypeTranslations = [
            'in_person' => 'حضوری',
            'online' => 'آنلاین',
            'phone' => 'تلفنی',
            'manual' => 'دستی'
        ];

        // ترجمه وضعیت پرداخت به فارسی
        $paymentStatusTranslations = [
            'pending' => 'در انتظار پرداخت',
            'paid' => 'پرداخت شده',
            'unpaid' => 'پرداخت نشده'
        ];

        // ترجمه روش پرداخت به فارسی
        $paymentMethodTranslations = [
            'online' => 'آنلاین',
            'cash' => 'نقدی',
            'card_to_card' => 'کارت به کارت',
            'pos' => 'پرداخت با کارت'
        ];

        return view('admin.panel.dashboard.index', [
            'totalDoctors' => Doctor::whereNull('deleted_at')->count(),
            'totalPatients' => User::where('user_type', 0)->whereNull('deleted_at')->count(),
            'totalSecretaries' => Secretary::whereNull('deleted_at')->count(),
            'totalManagers' => Manager::whereNull('deleted_at')->count(),
            'totalClinics' => Clinic::count(),
            'totalAppointments' => Appointment::whereNull('deleted_at')->count(),

            // نمودار ۱: نوبت‌ها در هر ماه
            'appointmentsByMonth' => Appointment::selectRaw('MONTH(appointment_date) as month, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('month')
                ->pluck('count', 'month')
                ->toArray(),

            // نمودار ۲: وضعیت نوبت‌ها
            'appointmentStatuses' => Appointment::selectRaw('status, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->mapWithKeys(function ($count, $status) use ($statusTranslations) {
                    return [$statusTranslations[$status] ?? $status => $count];
                })
                ->toArray(),

            // نمودار ۳: نوبت‌ها در روزهای هفته
            'appointmentsByDayOfWeek' => Appointment::selectRaw('WEEKDAY(appointment_date) as day, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('day')
                ->pluck('count', 'day')
                ->toArray(),

            // نمودار ۴: فعالیت کلینیک‌ها
            'clinicActivity' => Appointment::selectRaw('clinics.name as clinic_name, COUNT(appointments.id) as count')
                ->join('clinics', 'appointments.clinic_id', '=', 'clinics.id')
                ->whereNull('appointments.deleted_at')
                ->groupBy('clinics.id', 'clinics.name')
                ->pluck('count', 'clinic_name')
                ->toArray(),

            // نمودار ۵: توزیع تخصص‌های پزشکان
            'doctorSpecialties' => Doctor::selectRaw('specialties.name as specialty_name, COUNT(doctors.id) as count')
                ->join('specialties', 'doctors.specialty_id', '=', 'specialties.id')
                ->whereNull('doctors.deleted_at')
                ->groupBy('specialties.id', 'specialties.name')
                ->pluck('count', 'specialty_name')
                ->toArray(),

            // نمودار ۶: روند نوبت‌ها
            'appointmentsTrend' => Appointment::selectRaw('DATE(appointment_date) as date, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->where('appointment_date', '>=', now()->subDays(30))
                ->groupBy('date')
                ->pluck('count', 'date')
                ->toArray(),

            // نمودار ۷: مقایسه کلینیک‌ها
            'clinicComparison' => Appointment::selectRaw('
                    clinics.name as clinic_name,
                    COUNT(CASE WHEN appointments.status = "attended" THEN 1 END) as attended,
                    COUNT(CASE WHEN appointments.status = "cancelled" THEN 1 END) as cancelled,
                    COUNT(CASE WHEN appointments.status = "missed" THEN 1 END) as missed
                ')
                ->join('clinics', 'appointments.clinic_id', '=', 'clinics.id')
                ->whereNull('appointments.deleted_at')
                ->groupBy('clinics.id', 'clinics.name')
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->clinic_name => [
                        'حاضر شده' => $item->attended,
                        'لغو شده' => $item->cancelled,
                        'غایب' => $item->missed
                    ]];
                })
                ->toArray(),

            // نمودار ۸: وضعیت پرداخت‌ها
            'paymentStatus' => Appointment::selectRaw('payment_status, COUNT(*) as count')
                ->whereNull('appointments.deleted_at')
                ->groupBy('payment_status')
                ->pluck('count', 'payment_status')
                ->mapWithKeys(function ($count, $status) use ($paymentStatusTranslations) {
                    return [$paymentStatusTranslations[$status] ?? $status => $count];
                })
                ->toArray(),

            // نمودار ۹: آمار بازدید
            'visitorStats' => [
                'today' => Appointment::whereDate('created_at', today())->count(),
                'yesterday' => Appointment::whereDate('created_at', today()->subDay())->count(),
                'this_week' => Appointment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
                'last_week' => Appointment::whereBetween('created_at', [now()->subWeek()->startOfWeek(), now()->subWeek()->endOfWeek()])->count(),
                'this_month' => Appointment::whereMonth('created_at', now()->month)->count(),
            ],

            // برچسب‌های کلینیک
            'clinicActivityLabels' => Clinic::pluck('name')->toArray()
        ]);
    }
}
