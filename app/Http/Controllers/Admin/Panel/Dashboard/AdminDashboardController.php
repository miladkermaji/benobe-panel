<?php

namespace App\Http\Controllers\Admin\Panel\Dashboard;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Secretary;
use App\Models\Appointment;
use App\Models\Manager;
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

        // آمار هفتگی
        $weeklyStats = [
            'appointments' => Appointment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'users' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'operations' => Appointment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('status', 'attended')
                ->count(),
            'revenue' => Appointment::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->where('payment_status', 'paid')
                ->sum('final_price'),
            'new_doctors' => Doctor::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_medical_centers' => \App\Models\MedicalCenter::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count()
        ];

        // آمار ماهانه
        $monthlyStats = [
            'appointments' => Appointment::whereMonth('created_at', now()->month)->count(),
            'users' => User::whereMonth('created_at', now()->month)->count(),
            'operations' => Appointment::whereMonth('created_at', now()->month)
                ->where('status', 'attended')
                ->count(),
            'revenue' => Appointment::whereMonth('created_at', now()->month)
                ->where('payment_status', 'paid')
                ->sum('final_price'),
            'new_doctors' => Doctor::whereMonth('created_at', now()->month)->count(),
            'new_medical_centers' => \App\Models\MedicalCenter::whereMonth('created_at', now()->month)->count()
        ];

        return view('admin.panel.dashboard.index', [
            'totalDoctors' => Doctor::whereNull('deleted_at')->count(),
            'totalPatients' => User::where('user_type', 0)->whereNull('deleted_at')->count(),
            'totalSecretaries' => Secretary::whereNull('deleted_at')->count(),
            'totalManagers' => Manager::whereNull('deleted_at')->count(),
            'totalMedicalCenters' => \App\Models\MedicalCenter::count(),
            'totalAppointments' => Appointment::whereNull('deleted_at')->count(),
            'weeklyStats' => $weeklyStats,
            'monthlyStats' => $monthlyStats,

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

            // نمودار ۵: توزیع تخصص‌های پزشکان
            'doctorSpecialties' => Doctor::selectRaw('specialties.name as specialty_name, COUNT(doctors.id) as count')
                ->join('specialties', 'doctors.specialty_id', '=', 'specialties.id')
                ->whereNull('doctors.deleted_at')
                ->groupBy('specialties.id', 'specialties.name')
                ->orderBy('count', 'desc')
                ->limit(6)
                ->pluck('count', 'specialty_name')
                ->toArray(),

            // نمودار ۶: روند نوبت‌ها (به صورت هفتگی)
            'appointmentsTrend' => Appointment::selectRaw('
                    DATE_FORMAT(appointment_date, "%Y-%u") as week,
                    COUNT(*) as count
                ')
                ->whereNull('appointments.deleted_at')
                ->where('appointment_date', '>=', now()->subWeeks(12))
                ->groupBy('week')
                ->orderBy('week')
                ->pluck('count', 'week')
                ->toArray(),

            // نمودار ۷: مقایسه مراکز درمانی
            'medicalCenterComparison' => Appointment::selectRaw('
                    medical_centers.name as medical_center_name,
                    COUNT(CASE WHEN appointments.status = "attended" THEN 1 END) as attended,
                    COUNT(CASE WHEN appointments.status = "cancelled" THEN 1 END) as cancelled,
                    COUNT(CASE WHEN appointments.status = "missed" THEN 1 END) as missed
                ')
                ->join('medical_centers', 'appointments.medical_center_id', '=', 'medical_centers.id')
                ->whereNull('appointments.deleted_at')
                ->groupBy('medical_centers.id', 'medical_centers.name')
                ->orderBy(DB::raw('COUNT(appointments.id)'), 'desc')
                ->limit(5)
                ->get()
                ->mapWithKeys(function ($item) {
                    return [$item->medical_center_name => [
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

            // نمودار ۱۰: درآمد ماهانه
            'monthlyRevenue' => Appointment::selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    SUM(CASE WHEN payment_status = "paid" THEN final_price ELSE 0 END) as revenue
                ')
                ->whereNull('deleted_at')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('revenue', 'month')
                ->toArray(),

            // نمودار ۱۱: توزیع بیمه‌ها
            'insuranceDistribution' => Appointment::selectRaw('
                    insurances.name as insurance_name,
                    COUNT(appointments.id) as count
                ')
                ->join('insurances', 'appointments.insurance_id', '=', 'insurances.id')
                ->whereNull('appointments.deleted_at')
                ->groupBy('insurances.id', 'insurances.name')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->pluck('count', 'insurance_name')
                ->toArray(),

            // نمودار ۱۲: روند رشد کاربران
            'userGrowth' => User::selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as count
                ')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),

            // نمودار ۱۳: توزیع جنسیت بیماران
            'patientGenderDistribution' => User::where('user_type', 0)
                ->selectRaw('sex, COUNT(*) as count')
                ->groupBy('sex')
                ->pluck('count', 'sex')
                ->mapWithKeys(function ($count, $sex) {
                    return [$sex === 'male' ? 'مرد' : 'زن' => $count];
                })
                ->toArray(),

            // نمودار ۱۴: توزیع سنی بیماران
            'patientAgeDistribution' => User::where('user_type', 0)
                ->whereNotNull('date_of_birth')
                ->selectRaw('
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN "کمتر از 18 سال"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 30 THEN "18-30 سال"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 31 AND 45 THEN "31-45 سال"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 46 AND 60 THEN "46-60 سال"
                        ELSE "بیشتر از 60 سال"
                    END as age_group,
                    COUNT(*) as count
                ')
                ->groupBy('age_group')
                ->pluck('count', 'age_group')
                ->toArray(),

            // نمودار ۱۵: توزیع جغرافیایی بیماران (بهبود یافته)
            'patientGeographicDistribution' => User::where('user_type', 0)
                ->selectRaw('
                    zone.name as province,
                    COUNT(*) as count
                ')
                ->join('zone', 'users.zone_province_id', '=', 'zone.id')
                ->groupBy('zone.id', 'zone.name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->pluck('count', 'province')
                ->toArray(),

            // نمودار جدید: توزیع نوع نوبت‌ها
            'appointmentTypeDistribution' => Appointment::selectRaw('
                    appointment_type,
                    COUNT(*) as count
                ')
                ->whereNull('deleted_at')
                ->groupBy('appointment_type')
                ->pluck('count', 'appointment_type')
                ->mapWithKeys(function ($count, $type) use ($appointmentTypeTranslations) {
                    return [$appointmentTypeTranslations[$type] ?? $type => $count];
                })
                ->toArray(),

            // نمودار جدید: توزیع روش‌های پرداخت
            'paymentMethodDistribution' => Appointment::selectRaw('
                    payment_method,
                    COUNT(*) as count
                ')
                ->whereNull('deleted_at')
                ->groupBy('payment_method')
                ->pluck('count', 'payment_method')
                ->mapWithKeys(function ($count, $method) use ($paymentMethodTranslations) {
                    return [$paymentMethodTranslations[$method] ?? $method => $count];
                })
                ->toArray(),

            // نمودار جدید: روند رشد پزشکان
            'doctorGrowth' => Doctor::selectRaw('
                    DATE_FORMAT(created_at, "%Y-%m") as month,
                    COUNT(*) as count
                ')
                ->where('created_at', '>=', now()->subMonths(12))
                ->groupBy('month')
                ->orderBy('month')
                ->pluck('count', 'month')
                ->toArray(),

            // برچسب‌های کلینیک
            'clinicActivityLabels' => \App\Models\MedicalCenter::pluck('name')->toArray()
        ]);
    }
}
