<?php

namespace App\Http\Controllers\Mc\Panel\MyPerformance;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\ManualAppointment;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\CounselingAppointment;
use App\Http\Controllers\Mc\Controller;
use App\Models\Doctor;
use App\Models\MedicalCenter;
use App\Traits\HasSelectedClinic;
use App\Traits\HasSelectedDoctor;

class MyPerformanceController extends Controller
{
    use HasSelectedClinic;
    use HasSelectedDoctor;

    /**
     * نمایش صفحه اصلی عملکرد من
     */
    public function index()
    {
        $user = Auth::guard('doctor')->user() ??
                Auth::guard('secretary')->user() ??
                Auth::guard('medical_center')->user();

        if (!$user) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }

        // اگر کاربر مرکز درمانی باشد
        if (Auth::guard('medical_center')->check()) {
            /** @var MedicalCenter $medicalCenter */
            $medicalCenter = $user;
            $selectedDoctor = $medicalCenter->selectedDoctor;
            $selectedDoctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;

            $medicalCenters = collect([$medicalCenter]);
            $medicalCenterId = $medicalCenter->id;

            return view('mc.panel.my-performance.index', compact('medicalCenters', 'medicalCenter', 'medicalCenterId', 'selectedDoctorId'));
        }

        // برای دکتر و منشی (کد قبلی)
        $doctorId = $user instanceof \App\Models\Doctor ? $user->id : $user->doctor_id;
        $medicalCenter = $this->getSelectedMedicalCenter();
        $medicalCenterId = $this->getSelectedMedicalCenterId();

        $doctor = Doctor::with([
            'medicalCenters',
            'messengers',
            'reviews',
            'appointments' => function ($query) use ($medicalCenterId) {
                $query->when($medicalCenterId, function ($q) use ($medicalCenterId) {
                    $q->where('medical_center_id', $medicalCenterId);
                })
                ->whereDate('appointment_date', now()->toDateString());
            }
        ])->find($doctorId);

        $medicalCenters = \App\Models\MedicalCenter::whereHas('doctors', function ($query) use ($doctorId) {
            $query->where('doctor_id', $doctorId);
        })->get();

        return view('mc.panel.my-performance.index', compact('medicalCenters', 'medicalCenter', 'medicalCenterId'));
    }

    /**
     * دریافت داده‌های پویا برای صفحه عملکرد من
     */
    public function getPerformanceData(Request $request)
    {
        try {
            $user = Auth::guard('doctor')->user() ??
                    Auth::guard('secretary')->user() ??
                    Auth::guard('medical_center')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // اگر کاربر مرکز درمانی باشد
            if (Auth::guard('medical_center')->check()) {
                /** @var MedicalCenter $medicalCenter */
                $medicalCenter = $user;
                $selectedDoctor = $medicalCenter->selectedDoctor;
                $selectedDoctorId = $selectedDoctor ? $selectedDoctor->doctor_id : null;

                if (!$selectedDoctorId) {
                    return response()->json(['error' => 'هیچ پزشکی انتخاب نشده است'], 400);
                }

                $doctor = Doctor::with([
                    'clinics',
                    'messengers',
                    'reviews',
                    'appointments' => function ($query) use ($medicalCenter) {
                        $query->where('medical_center_id', $medicalCenter->id)
                              ->whereDate('appointment_date', now()->toDateString());
                    }
                ])->findOrFail($selectedDoctorId);
            } else {
                // برای دکتر و منشی
                $doctorId = $user instanceof \App\Models\Doctor ? $user->id : $user->doctor_id;
                $doctor = Doctor::with([
                    'clinics',
                    'messengers',
                    'reviews',
                    'appointments' => function ($query) {
                        $query->whereDate('appointment_date', now()->toDateString());
                    }
                ])->findOrFail($doctorId);
            }

            // محاسبه امتیاز عملکرد (فرضی)
            $performanceScore = $this->calculatePerformanceScore($doctor);

            // بررسی شهر محل طبابت
            $city = $doctor->city ?? 'نامشخص';
            $cityStatus = !empty($doctor->city);

            // بررسی وضعیت ویزیت آنلاین
            $onlineVisitEnabled = $doctor->appointmentConfig && $doctor->appointmentConfig->auto_scheduling ? true : false;

            // تعداد نظرات
            $reviewsCount = $doctor->reviews ? $doctor->reviews->count() : 0;
            $hasEnoughReviews = $reviewsCount >= 150;

            // وضعیت آنلاین بودن
            $isOnline = $doctor->appointmentConfig && $doctor->appointmentConfig->auto_scheduling ? true : false;

            // نوبت‌دهی حضوری برای امروز
            $hasInPersonAppointmentsToday = $doctor->appointments ? $doctor->appointments->isNotEmpty() : false;

            // آدرس واضح
            $hasClearAddress = !empty($doctor->address) && !preg_match('/\d{10,}/', $doctor->address);

            // شماره تلفن در آدرس
            $hasPhoneInAddress = !empty($doctor->address) && preg_match('/\d{10,}/', $doctor->address);

            // سایر فیلدهای مورد نیاز
            $hasValidOfficePhone = !empty($doctor->office_phone);
            $hasClinicLocationSet = $doctor->clinics ? $doctor->clinics->isNotEmpty() : false;
            $hasSpecialties = !empty($doctor->specialties);
            $hasIrrelevantSpecialty = false; // منطق بررسی عنوان بی‌ربط
            $hasLowerDegrees = false; // منطق بررسی درجه‌های پایین‌تر
            $hasProperSpecialtyTitle = $this->checkProperSpecialtyTitle($doctor->specialties ?? []);
            $hasRealisticTitles = !$this->checkUnrealisticTitles($doctor->specialties ?? []);
            $satisfactionRate = $this->calculateSatisfactionRate($doctor);
            $hasManipulatedReviews = $this->checkManipulatedReviews($doctor);
            $hasProfilePicture = !empty($doctor->profile_picture);
            $hasClinicGallery = $this->checkClinicGallery($doctor->clinics ?? collect());
            $hasFacilityImages = $this->checkFacilityImages($doctor->clinics ?? collect());
            $hasBiography = !empty($doctor->description);
            $hasKeywordsInBiography = $this->checkKeywordsInBiography($doctor->description);
            $hasMultipleMessengers = $doctor->messengers ? $doctor->messengers->count() > 1 : false;
            $hasSecureCall = false; // منطق بررسی تماس امن
            $hasMissedReports = $this->checkMissedReports($doctor);

            // آماده‌سازی لیست مطب‌ها
            $clinics = collect();
            if ($doctor->clinics) {
                $clinics = $doctor->clinics->map(function ($clinic) {
                    return [
                        'name' => $clinic->name,
                        'url' => route('mc-clinic-edit', $clinic->id)
                    ];
                });
            }

            return response()->json([
                'doctor_name' => $doctor->first_name . ' ' . $doctor->last_name,
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
                'clinics' => $clinics,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getPerformanceData: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'خطا در دریافت اطلاعات: ' . $e->getMessage()], 500);
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
        if (!$clinics || $clinics->isEmpty()) {
            return false;
        }
        return $clinics->every(function ($clinic) {
            return !empty($clinic->gallery);
        });
    }

    private function checkFacilityImages($clinics)
    {
        // بررسی تصاویر امکانات
        if (!$clinics || $clinics->isEmpty()) {
            return false;
        }
        return $clinics->every(function ($clinic) {
            return !empty($clinic->facility_images);
        });
    }

    private function checkKeywordsInBiography($biography)
    {
        // بررسی کلمات کلیدی در بیوگرافی
        if (empty($biography)) {
            return false;
        }
        return preg_match('/(بیماری|درمان|پروسیجر)/u', $biography);
    }

    private function checkMissedReports($doctor)
    {
        // بررسی گزارش عدم مراجعه
        if (!$doctor) {
            return false;
        }
        return Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'missed')
            ->exists();
    }

    /**
     * نمایش صفحه نمودارها
     */
    public function chart()
    {
        return view('mc.panel.my-performance.chart');
    }

    /**
     * دریافت داده‌های نمودار
     */
    public function getChartData(Request $request)
    {
        try {
            $user = Auth::guard('doctor')->user() ??
                    Auth::guard('secretary')->user() ??
                    Auth::guard('medical_center')->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $medicalCenterId = $request->get('medical_center_id', 'default');
            $selectedDoctorId = $request->get('doctor_id');

            // اگر کاربر مرکز درمانی باشد
            if (Auth::guard('medical_center')->check()) {
                /** @var MedicalCenter $medicalCenter */
                $medicalCenter = $user;

                // اگر پزشک انتخاب شده باشد، از آن استفاده کن
                if ($selectedDoctorId) {
                    $doctorId = $selectedDoctorId;
                    $medicalCenterCondition = function ($query) use ($medicalCenter) {
                        $query->where('medical_center_id', $medicalCenter->id);
                    };
                } else {
                    // اطلاعات کل مرکز درمانی
                    $medicalCenterCondition = function ($query) use ($medicalCenter) {
                        $query->where('medical_center_id', $medicalCenter->id);
                    };

                    // برای نمودارها، از اولین پزشک فعال استفاده کن
                    $firstDoctor = $medicalCenter->doctors()->where('is_active', true)->first();
                    if (!$firstDoctor) {
                        return response()->json(['error' => 'هیچ پزشک فعالی در این مرکز درمانی وجود ندارد'], 400);
                    }
                    $doctorId = $firstDoctor->id;
                }
            } else {
                // برای دکتر و منشی
                $doctorId = $user instanceof \App\Models\Doctor ? $user->id : $user->doctor_id;

                // Validate medical_center_id
                if ($medicalCenterId !== 'default' && !is_numeric($medicalCenterId)) {
                    return response()->json(['error' => 'مقدار medical_center_id نامعتبر است'], 400);
                }

                $medicalCenterCondition = function ($query) use ($medicalCenterId) {
                    if ($medicalCenterId === 'default') {
                        $query->whereNull('medical_center_id');
                    } else {
                        $query->whereNotNull('medical_center_id')->where('medical_center_id', $medicalCenterId);
                    }
                };
            }

            // 1. نوبت‌های معمولی (appointments) - هفتگی
            $appointmentsQuery = Appointment::where('doctor_id', $doctorId)
                ->where($medicalCenterCondition);

            $appointments = $appointmentsQuery
                ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%u') as week,
                         COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
                         COUNT(CASE WHEN status = 'attended' THEN 1 END) as attended_count,
                         COUNT(CASE WHEN status = 'missed' THEN 1 END) as missed_count,
                         COUNT(CASE WHEN status = 'cancelled' THEN 1 END) as cancelled_count")
                ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
                ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
                ->get();

            // 2. درآمد ماهانه
            $monthlyIncomeQuery = Appointment::where('doctor_id', $doctorId)
                ->where($medicalCenterCondition);

            $monthlyIncome = $monthlyIncomeQuery
                ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month,
                         COALESCE(SUM(CASE WHEN payment_status = 'paid' THEN final_price ELSE 0 END), 0) as total_paid_income,
                         COALESCE(SUM(CASE WHEN payment_status = 'unpaid' THEN final_price ELSE 0 END), 0) as total_unpaid_income")
                ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
                ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
                ->get();

            // 3. بیماران جدید - هفتگی
            $newPatientsQuery = Appointment::where('doctor_id', $doctorId)
                ->where($medicalCenterCondition)
                ->where('patientable_type', 'App\\Models\\User')
                ->join('users', 'appointments.patientable_id', '=', 'users.id');

            $newPatients = $newPatientsQuery
                ->selectRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%u') as week,
                         COUNT(DISTINCT appointments.patientable_id) as total_patients")
                ->groupByRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%u')")
                ->orderByRaw("DATE_FORMAT(appointments.appointment_date, '%Y-%u')")
                ->get();

            // 4. نوبت‌های مشاوره - هفتگی
            $counselingQuery = CounselingAppointment::where('doctor_id', $doctorId)
                ->where($medicalCenterCondition);

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
                ->where($medicalCenterCondition)
                ->where('appointment_type', 'manual');

            $manualAppointments = $manualQuery
                ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%u') as week,
                         COUNT(CASE WHEN status = 'scheduled' THEN 1 END) as scheduled_count,
                         COUNT(CASE WHEN status = 'confirmed' THEN 1 END) as confirmed_count")
                ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
                ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%u')")
                ->get();

            // 6. درآمد کلی
            $totalIncomeQuery = Appointment::where('doctor_id', $doctorId)
                ->where($medicalCenterCondition)
                ->where('payment_status', 'paid')
                ->where('status', 'attended');

            $totalIncome = $totalIncomeQuery
                ->selectRaw("DATE_FORMAT(appointment_date, '%Y-%m') as month,
                         COALESCE(SUM(final_price), 0) as total_income")
                ->groupByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
                    ->orderByRaw("DATE_FORMAT(appointment_date, '%Y-%m')")
                ->get();

            return response()->json([
                'appointments' => $appointments,
                'monthly_income' => $monthlyIncome,
                'new_patients' => $newPatients,
                'counseling_appointments' => $counselingAppointments,
                'manual_appointments' => $manualAppointments,
                'total_income' => $totalIncome,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in getChartData: ' . $e->getMessage());
            return response()->json(['error' => 'خطا در دریافت داده‌های نمودار'], 500);
        }
    }
}
