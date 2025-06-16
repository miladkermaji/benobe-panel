<?php

namespace App\Http\Controllers\Dr\Panel\MyPerformance;

use App\Models\User;
use App\Models\Clinic;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\ManualAppointment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\CounselingAppointment;
use App\Http\Controllers\Dr\Controller;
use App\Models\Doctor;
use App\Traits\HasSelectedClinic;

class MyPerformanceController extends Controller
{
    use HasSelectedClinic;

    /**
     * نمایش صفحه اصلی عملکرد من
     */
    public function index()
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }

        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        $clinic = $this->getSelectedClinic();
        $clinicId = $this->getSelectedClinicId();

        $doctor = Doctor::with([
            'clinics',
            'messengers',
            'reviews',
            'appointments' => function ($query) use ($clinicId) {
                $query->when($clinicId, function ($q) use ($clinicId) {
                    $q->where('clinic_id', $clinicId);
                })
                ->whereDate('appointment_date', now()->toDateString());
            }
        ])->find($doctorId);

        $clinics = Clinic::where('doctor_id', $doctorId)->get();
        return view('dr.panel.my-performance.index', compact('clinics', 'clinic', 'clinicId'));
    }

    /**
     * دریافت داده‌های پویا برای صفحه عملکرد من
     */
    public function getPerformanceData(Request $request)
    {
        try {
            $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
            if (!$doctor) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;

            $doctor = Doctor::with([
                'clinics',
                'messengers',
                'reviews',
                'appointments' => function ($query) {
                    $query->whereDate('appointment_date', now()->toDateString());
                }
            ])->findOrFail($doctorId);

            // محاسبه امتیاز عملکرد (فرضی)
            $performanceScore = $this->calculatePerformanceScore($doctor);

            // بررسی شهر محل طبابت
            $city = $doctor->city ?? 'نامشخص';
            $cityStatus = !empty($doctor->city);

            // بررسی وضعیت ویزیت آنلاین
            $onlineVisitEnabled = $doctor->appointmentConfig->auto_scheduling ?? false;

            // تعداد نظرات
            $reviewsCount = $doctor->reviews->count();
            $hasEnoughReviews = $reviewsCount >= 150;

            // وضعیت آنلاین بودن
            $isOnline = $doctor->appointmentConfig->auto_scheduling ?? false;

            // نوبت‌دهی حضوری برای امروز
            $hasInPersonAppointmentsToday = $doctor->appointments->isNotEmpty();

            // آدرس واضح
            $hasClearAddress = !empty($doctor->address) && !preg_match('/\d{10,}/', $doctor->address);

            // شماره تلفن در آدرس
            $hasPhoneInAddress = !empty($doctor->address) && preg_match('/\d{10,}/', $doctor->address);

            // صحت تلفن مطب
            $hasValidOfficePhone = !empty($doctor->office_phone) && preg_match('/^09\d{9}$/', $doctor->office_phone);

            // موقعیت مطب
            $hasClinicLocationSet = $doctor->clinics->every(function ($clinic) {
                return !empty($clinic->latitude) && !empty($clinic->longitude);
            });

            // تخصص‌ها و درجه علمی
            $hasSpecialties = !empty($doctor->specialties);

            // عنوان بی‌ربط در تخصص
            $hasIrrelevantSpecialty = $this->checkIrrelevantSpecialty($doctor->specialties);

            // سایر موارد
            $hasLowerDegrees = !empty($doctor->lower_degrees);
            $hasProperSpecialtyTitle = $this->checkProperSpecialtyTitle($doctor->specialties);
            $hasRealisticTitles = !$this->checkUnrealisticTitles($doctor->specialties);
            $satisfactionRate = $this->calculateSatisfactionRate($doctor);
            $hasManipulatedReviews = $this->checkManipulatedReviews($doctor);
            $hasProfilePicture = !empty($doctor->profile_picture);
            $hasClinicGallery = $this->checkClinicGallery($doctor->clinics);
            $hasFacilityImages = $this->checkFacilityImages($doctor->clinics);
            $hasBiography = !empty($doctor->biography);
            $hasKeywordsInBiography = $this->checkKeywordsInBiography($doctor->biography);
            $hasMultipleMessengers = $doctor->messengers->count() >= 2;
            $hasSecureCall = $doctor->secure_call_enabled ?? false;
            $hasMissedReports = $this->checkMissedReports($doctor);

            // تولید URL برای کلینیک‌ها
            $clinicsData = $doctor->clinics->map(function ($clinic) {
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
        } catch (\Exception $e) {
            Log::error('Error in getPerformanceData: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }
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
        $clinics = Clinic::where('doctor_id', Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id)->get();
        return view('dr.panel.my-performance.chart.index', compact('clinics'));
    }

    public function getChartData(Request $request)
    {
        $clinicId =
$this->getSelectedClinicId()
 ?? 'default';


        // Convert empty string, null, or 'null' to 'default'
        if (empty($clinicId) || $clinicId === 'null') {
            $clinicId = 'default';
        }

        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id;

        // Validate clinic_id
        if ($clinicId !== 'default' && !is_numeric($clinicId)) {
            return response()->json(['error' => 'مقدار clinic_id نامعتبر است'], 400);
        }

        $clinicCondition = function ($query) use ($clinicId) {
            if ($clinicId === 'default') {
                $query->whereNull('clinic_id');
            } else {
                $query->whereNotNull('clinic_id')->where('clinic_id', $clinicId);
            }
        };

        // 1. نوبت‌های معمولی (appointments) - هفتگی
        $appointmentsQuery = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition);

        $appointments = $appointmentsQuery
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%u') as week,
                         COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
                         COUNT(CASE WHEN status = 'attended' THEN 1 END) as attended_count,
                         COUNT(CASE WHEN status = 'missed' THEN 1 END) as missed_count,
                         COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->get();

        // 2. درآمد ماهانه (بدون تغییر)
        $monthlyIncomeQuery = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition);

        $monthlyIncome = $monthlyIncomeQuery
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month,
                         COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN final_price ELSE 0 END), 0) as total_paid_income,
                         COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN final_price ELSE 0 END), 0) as total_unpaid_income")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
            ->get();

        // 3. بیماران جدید - هفتگی
        $newPatientsQuery = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->join('users', 'appointments.patient_id', '=', 'users.id');

        $newPatients = $newPatientsQuery
            ->selectRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%u') as week,
                         COUNT(DISTINCT appointments.patient_id) as total_patients")
            ->groupByRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%u')")
            ->orderByRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%u')")
            ->get();

        // 4. نوبت‌های مشاوره - هفتگی
        $counselingQuery = CounselingAppointment::where('doctor_id', $doctorId)
            ->where($clinicCondition);

        $counselingAppointments = $counselingQuery
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%u') as week,
                         COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
                         COUNT(CASE WHEN status = 'attended' THEN 1 END) as attended_count,
                         COUNT(CASE WHEN status = 'missed' THEN 1 END) as missed_count,
                         COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->get();

        // 5. نوبت‌های دستی - هفتگی
        $manualQuery = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->where('appointment_type', 'manual');

        $manualAppointments = $manualQuery
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%u') as week,
                         COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
                         COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_count")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->get();

        // 6. درآمد کلی (بدون تغییر)
        $totalIncomeQuery = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition)
            ->where('payment_status', 'paid')
            ->where('status', 'attended');

        $totalIncome = $totalIncomeQuery
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month,
                         COALESCE(SUM(final_price), 0) as total_income")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
            ->union(
                CounselingAppointment::where('doctor_id', $doctorId)
                    ->where($clinicCondition)
                    ->where('payment_status', 'paid')
                    ->where('status', 'attended')
                    ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month,
                         COALESCE(SUM(fee), 0) as total_income")
                    ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
            )
            ->get()
            ->groupBy('month')
            ->map(function ($group) {
                return [
                    'month' => $group->first()->month,
                    'total' => (float)$group->sum('total_income')
                ];
            })
            ->values();

        // 7. انواع نوبت‌ها - هفتگی
        $appointmentTypesQuery = Appointment::where('doctor_id', $doctorId)
            ->where($clinicCondition);

        $appointmentTypes = $appointmentTypesQuery
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%u') as week,
                         COUNT(CASE WHEN appointment_type = 'in_person' THEN 1 END) as in_person_count,
                         COUNT(CASE WHEN appointment_type = 'online' THEN 1 END) as online_count,
                         COUNT(CASE WHEN appointment_type = 'phone' THEN 1 END) as phone_count")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->get();

        // اضافه کردن داده‌های مشاوره به انواع نوبت‌ها
        $counselingTypesQuery = CounselingAppointment::where('doctor_id', $doctorId)
            ->where($clinicCondition);

        $counselingTypes = $counselingTypesQuery
            ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%u') as week,
                         COUNT(CASE WHEN appointment_type = 'video' THEN 1 END) as video_count,
                         COUNT(CASE WHEN appointment_type = 'text' THEN 1 END) as text_count")
            ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
            ->get();

        // ترکیب داده‌های نوبت‌های معمولی و مشاوره
        $combinedAppointmentTypes = $appointmentTypes->map(function ($item) use ($counselingTypes) {
            $counselingData = $counselingTypes->firstWhere('week', $item->week);
            return [
                'week' => $item->week,
                'in_person' => (int)$item->in_person_count,
                'online' => (int)$item->online_count,
                'phone' => (int)$item->phone_count,
                'video' => $counselingData ? (int)$counselingData->video_count : 0,
                'text' => $counselingData ? (int)$counselingData->text_count : 0
            ];
        });

        $response = [
            'appointments' => $appointments->map(function ($item) {
                return [
                    'month' => $item->week,
                    'scheduled' => (int)$item->scheduled_count,
                    'attended' => (int)$item->attended_count,
                    'missed' => (int)$item->missed_count,
                    'cancelled' => (int)$item->cancelled_count
                ];
            })->values()->toArray(),
            'monthlyIncome' => $monthlyIncome->map(function ($item) {
                return [
                    'month' => $item->month,
                    'paid' => (float)$item->total_paid_income,
                    'unpaid' => (float)$item->total_unpaid_income
                ];
            })->values()->toArray(),
            'newPatients' => $newPatients->map(function ($item) {
                return [
                    'month' => $item->week,
                    'count' => (int)$item->total_patients
                ];
            })->values()->toArray(),
            'appointmentTypes' => $combinedAppointmentTypes->map(function ($item) {
                return [
                    'month' => $item['week'],
                    'in_person' => $item['in_person'],
                    'online' => $item['online'],
                    'phone' => $item['phone'],
                    'video' => $item['video'],
                    'text' => $item['text']
                ];
            })->values()->toArray(),
            'counselingAppointments' => $counselingAppointments->map(function ($item) {
                return [
                    'month' => $item->week,
                    'scheduled' => (int)$item->scheduled_count,
                    'attended' => (int)$item->attended_count,
                    'missed' => (int)$item->missed_count,
                    'cancelled' => (int)$item->cancelled_count
                ];
            })->values()->toArray(),
            'manualAppointments' => $manualAppointments->map(function ($item) {
                return [
                    'month' => $item->week,
                    'scheduled' => (int)$item->scheduled_count,
                    'confirmed' => (int)$item->confirmed_count
                ];
            })->values()->toArray(),
            'totalIncome' => $totalIncome->toArray(),
        ];

        return response()->json($response);
    }
}
