<?php

namespace App\Http\Controllers\Dr\Panel\MyPerformance;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;

class MyPerformanceController extends Controller
{
    /**
     * نمایش صفحه اصلی عملکرد من
     */
    public function index()
    {
        $clinics = Clinic::where('doctor_id', Auth::guard('doctor')->user()->id)->get();
        return view('dr.panel.my-performance.index', compact('clinics'));
    }

    /**
     * دریافت داده‌های پویا برای صفحه عملکرد من
     */
    public function getPerformanceData(Request $request)
    {
        $doctor = Auth::guard('doctor')->user();
        $clinics = Clinic::where('doctor_id', $doctor->id)->get();

        // محاسبه امتیاز عملکرد (فرضی)
        $performanceScore = $this->calculatePerformanceScore($doctor);

        // بررسی شهر محل طبابت
        $city = $doctor->city ?? 'نامشخص';
        $cityStatus = !empty($doctor->city);

        // بررسی وضعیت ویزیت آنلاین
        $onlineVisitEnabled = $doctor->online_visit_enabled ?? false;

        // تعداد نظرات (فرضی)
        $reviewsCount = $doctor->reviews()->count() ?? 0;
        $hasEnoughReviews = $reviewsCount >= 150;

        // وضعیت آنلاین بودن
        $isOnline = $doctor->is_online ?? false;

        // نوبت‌دهی حضوری برای امروز
        $hasInPersonAppointmentsToday = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', now()->toDateString())
            ->exists();

        // آدرس واضح
        $hasClearAddress = !empty($doctor->address) && !preg_match('/\d{10,}/', $doctor->address);

        // شماره تلفن در آدرس
        $hasPhoneInAddress = !empty($doctor->address) && preg_match('/\d{10,}/', $doctor->address);

        // صحت تلفن مطب
        $hasValidOfficePhone = !empty($doctor->office_phone) && preg_match('/^09\d{9}$/', $doctor->office_phone);

        // موقعیت مطب
        $hasClinicLocationSet = $clinics->every(function ($clinic) {
            return !empty($clinic->latitude) && !empty($clinic->longitude);
        });

        // تخصص‌ها و درجه علمی
        $hasSpecialties = !empty($doctor->specialties);

        // عنوان بی‌ربط در تخصص
        $hasIrrelevantSpecialty = $this->checkIrrelevantSpecialty($doctor->specialties);

        // سایر موارد (فرضی)
        $hasLowerDegrees = !empty($doctor->lower_degrees);
        $hasProperSpecialtyTitle = $this->checkProperSpecialtyTitle($doctor->specialties);
        $hasRealisticTitles = !$this->checkUnrealisticTitles($doctor->specialties);
        $satisfactionRate = $this->calculateSatisfactionRate($doctor);
        $hasManipulatedReviews = $this->checkManipulatedReviews($doctor);
        $hasProfilePicture = !empty($doctor->profile_picture);
        $hasClinicGallery = $this->checkClinicGallery($clinics);
        $hasFacilityImages = $this->checkFacilityImages($clinics);
        $hasBiography = !empty($doctor->biography);
        $hasKeywordsInBiography = $this->checkKeywordsInBiography($doctor->biography);
        $hasMultipleMessengers = $this->checkMultipleMessengers($doctor);
        $hasSecureCall = $doctor->secure_call_enabled ?? false;
        $hasMissedReports = $this->checkMissedReports($doctor);

        // تولید URL برای کلینیک‌ها
        $clinicsData = $clinics->map(function ($clinic) {
            return [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'url' => route('activation-doctor-clinic', ['clinic' => $clinic->id]),
            ];
        });

        return response()->json([
            'doctor_name' => $doctor->full_name,
            'performance_score' => $performanceScore,
            'city' => $city,
            'city_status' => $cityStatus,
            'online_visit_enabled' => $onlineVisitEnabled,
            'reviews_count' => $reviewsCount,
            'has_enough_reviews' => $hasEnoughReviews,
            'is_online' => $isOnline,
            'has_in_person_appointments_today' => $hasInPersonAppointmentsToday,
            'has_clear_address' => $hasClearAddress,
            'has_phone_in_address' => $hasPhoneInAddress,
            'has_valid_office_phone' => $hasValidOfficePhone,
            'has_clinic_location_set' => $hasClinicLocationSet,
            'has_specialties' => $hasSpecialties,
            'has_irrelevant_specialty' => $hasIrrelevantSpecialty,
            'has_lower_degrees' => $hasLowerDegrees,
            'has_proper_specialty_title' => $hasProperSpecialtyTitle,
            'has_realistic_titles' => $hasRealisticTitles,
            'satisfaction_rate' => $satisfactionRate,
            'has_manipulated_reviews' => $hasManipulatedReviews,
            'has_profile_picture' => $hasProfilePicture,
            'has_clinic_gallery' => $hasClinicGallery,
            'has_facility_images' => $hasFacilityImages,
            'has_biography' => $hasBiography,
            'has_keywords_in_biography' => $hasKeywordsInBiography,
            'has_multiple_messengers' => $hasMultipleMessengers,
            'has_secure_call' => $hasSecureCall,
            'has_missed_reports' => $hasMissedReports,
            'clinics' => $clinicsData,
        ]);
    }
    /**
     * متدهای کمکی برای محاسبات (فرضی - باید منطق خودتون رو اعمال کنید)
     */
    private function calculatePerformanceScore($doctor)
    {
        // منطق محاسبه امتیاز عملکرد
        return 46; // مقدار فرضی
    }

    private function checkIrrelevantSpecialty($specialties)
    {
        // بررسی عنوان بی‌ربط
        return false; // فرضی
    }

    private function checkProperSpecialtyTitle($specialties)
    {
        // بررسی عنوان مناسب تخصص
        return true; // فرضی
    }

    private function checkUnrealisticTitles($specialties)
    {
        // بررسی عناوین غیرواقعی
        return false; // فرضی
    }

    private function calculateSatisfactionRate($doctor)
    {
        // محاسبه نرخ رضایت
        return 80; // فرضی
    }

    private function checkManipulatedReviews($doctor)
    {
        // بررسی دستکاری نظرات
        return false; // فرضی
    }

    private function checkClinicGallery($clinics)
    {
        // بررسی گالری تصاویر مطب
        return $clinics->every(function ($clinic) {
            return !empty($clinic->gallery);
        });
    }

    private function checkFacilityImages($clinics)
    {
        // بررسی تصاویر امکانات
        return $clinics->every(function ($clinic) {
            return !empty($clinic->facility_images);
        });
    }

    private function checkKeywordsInBiography($biography)
    {
        // بررسی کلمات کلیدی در بیوگرافی
        return !empty($biography) && preg_match('/(بیماری|درمان|پروسیجر)/u', $biography);
    }

    private function checkMultipleMessengers($doctor)
    {
        // بررسی تنوع پیام‌رسان‌ها
        return count($doctor->messengers ?? []) >= 2;
    }

    private function checkMissedReports($doctor)
    {
        // بررسی گزارش عدم مراجعه
        return Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'missed')
            ->exists();
    }

    /**
     * متدهای موجود (بدون تغییر)
     */
    public function chart()
    {
        $clinics = Clinic::where('doctor_id', Auth::guard('doctor')->user()->id)->get();
        return view('dr.panel.my-performance.chart.index', compact('clinics'));
    }

    public function getChartData(Request $request)
    {
        $clinicId = $request->input('clinic_id', 'default');
        $doctorId = Auth::guard('doctor')->user()->id;

        $clinicCondition = function ($query) use ($clinicId) {
            if ($clinicId === 'default') {
                $query->whereNull('clinic_id');
            } else {
                $query->where('clinic_id', $clinicId);
            }
        };

        Log::info('داده‌های دریافتی برای نمودارها:', [
            'clinicId' => $clinicId,
            'doctorId' => $doctorId,
        ]);

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

        $monthlyIncome = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->selectRaw("DATE_FORMAT(appointment_date, '%m') as month,
                     COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN fee ELSE 0 END), 0) as total_paid_income,
                     COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN fee ELSE 0 END), 0) as total_unpaid_income")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->get();

        $newPatients = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->join('users', 'appointments.patient_id', '=', 'users.id')
            ->selectRaw("DATE_FORMAT(appointments.appointment_date, '%m') as month,
                     COUNT(DISTINCT appointments.patient_id) as total_patients")
            ->groupByRaw("DATE_FORMAT(appointments.appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointments.appointment_date, '%m')")
            ->get();

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

        $averageDurationByMonth = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->whereNotNull('duration')
            ->selectRaw("DATE_FORMAT(appointment_date, '%m') as month,
                     AVG(duration) as average_duration")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%m')")
            ->get();

        Log::info('نتایج داده‌های نمودار:', [
            'appointments'             => $appointments->toArray(),
            'monthlyIncome'            => $monthlyIncome->toArray(),
            'newPatients'              => $newPatients->toArray(),
            'appointmentStatusByMonth' => $appointmentStatusByMonth->toArray(),
            'averageDurationByMonth'   => $averageDurationByMonth->toArray(),
        ]);

        return response()->json([
            'appointments'             => $appointments->isEmpty() ? [] : $appointments,
            'monthlyIncome'            => $monthlyIncome->isEmpty() ? [] : $monthlyIncome,
            'newPatients'              => $newPatients->isEmpty() ? [] : $newPatients,
            'appointmentStatusByMonth' => $appointmentStatusByMonth->isEmpty() ? [] : $appointmentStatusByMonth,
            'averageDurationByMonth'   => $averageDurationByMonth->isEmpty() ? [] : $averageDurationByMonth,
        ]);
    }
}
