<?php

namespace App\Http\Controllers\Dr\Panel\MyPerformance;

use App\Models\User;
use App\Models\Dr\Clinic;
use Illuminate\Http\Request;
use App\Models\Dr\Appointment;
use Illuminate\Support\Facades\Auth;

class MyPerformanceController
{
    /**
     * نمایش صفحه اصلی آمار و نمودارها
     */
    public function chart()
    {
        $clinics = Clinic::where('doctor_id', Auth::guard('doctor')->user()->id)->get();
        return view('dr.panel.my-performance.chart.index', compact('clinics'));
    }

    /**
     * دریافت داده‌های نمودارها به‌صورت پویا
     */
    public function getChartData(Request $request)
    {
        // گرفتن Clinic ID از پارامتر URL یا پیش‌فرض از LocalStorage
        $clinicId = $request->input('clinic_id', 'default');
        $doctorId = Auth::guard('doctor')->user()->id;

        // شرط مشترک برای کلینیک
        $clinicCondition = function ($query) use ($clinicId) {
            if ($clinicId === 'default') {
                $query->whereNull('clinic_id');
            } else {
                $query->where('clinic_id', $clinicId);
            }
        };

        // لاگ کلینیک و دکتر برای اطمینان
        \Log::info('داده‌های دریافتی برای نمودارها:', [
            'clinicId' => $clinicId,
            'doctorId' => $doctorId,
        ]);

        /**
         * 📊 نمودار ۱: تعداد ویزیت‌ها به تفکیک وضعیت و ماه
         */
        $appointments = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->selectRaw("DATE_FORMAT(appointment_date, '%m') as month,
                     COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
                     COUNT(CASE WHEN status = 'attended' THEN 1 END) as attended_count,
                     COUNT(CASE WHEN status = 'missed' THEN 1 END) as missed_count,
                     COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->get();

        /**
         * 💰 نمودار ۲: درآمد ماهانه به تفکیک پرداخت‌شده و پرداخت‌نشده
         */
        $monthlyIncome = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->selectRaw("DATE_FORMAT(appointment_date, '%m') as month, 
                     COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN fee ELSE 0 END), 0) as total_paid_income,
                     COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN fee ELSE 0 END), 0) as total_unpaid_income")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->get();

        /**
         * 👨‍⚕️ نمودار ۳: تعداد بیماران جدید بر اساس ماه
         */
        $newPatients = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->join('users', 'appointments.patient_id', '=', 'users.id')
            ->selectRaw("DATE_FORMAT(appointments.appointment_date, '%m') as month, 
                     COUNT(DISTINCT appointments.patient_id) as total_patients")
            ->groupByRaw("DATE_FORMAT(appointments.appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointments.appointment_date, '%m')")
            ->get();

        /**
         * 📈 نمودار ۴: تعداد وضعیت‌های نوبت‌ها به تفکیک ماه
         */
        $appointmentStatusByMonth = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->selectRaw("DATE_FORMAT(appointment_date, '%m') as month,
                     COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
                     COUNT(CASE WHEN status = 'attended' THEN 1 END) as attended_count,
                     COUNT(CASE WHEN status = 'missed' THEN 1 END) as missed_count,
                     COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->get();

        /**
         * 🕒 نمودار ۵: میانگین مدت زمان نوبت‌ها به تفکیک ماه
         */
        $averageDurationByMonth = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->whereNotNull('duration')
            ->selectRaw("DATE_FORMAT(appointment_date, '%m') as month, 
                     AVG(duration) as average_duration")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->get();

        /**
         * 📝 لاگ اطلاعات نهایی برای اطمینان
         */
        \Log::info('نتایج داده‌های نمودار:', [
            'appointments' => $appointments->toArray(),
            'monthlyIncome' => $monthlyIncome->toArray(),
            'newPatients' => $newPatients->toArray(),
            'appointmentStatusByMonth' => $appointmentStatusByMonth->toArray(),
            'averageDurationByMonth' => $averageDurationByMonth->toArray(),
        ]);

        /**
         * ✅ ارسال داده‌ها به فرانت به‌صورت JSON
         */
        return response()->json([
            'appointments' => $appointments->isEmpty() ? [] : $appointments,
            'monthlyIncome' => $monthlyIncome->isEmpty() ? [] : $monthlyIncome,
            'newPatients' => $newPatients->isEmpty() ? [] : $newPatients,
            'appointmentStatusByMonth' => $appointmentStatusByMonth->isEmpty() ? [] : $appointmentStatusByMonth,
            'averageDurationByMonth' => $averageDurationByMonth->isEmpty() ? [] : $averageDurationByMonth,
        ]);
    }

}
