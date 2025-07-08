<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Doctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class DoctorListingController extends Controller
{
    public function getDoctors(Request $request)
    {
        try {
            // اعتبارسنجی ورودی‌ها
            $validated = $request->validate([
                'province_id'                => 'nullable|integer|exists:zone,id',
                'specialty_id'               => 'nullable|integer|exists:specialties,id',
                'sex'                        => 'nullable|in:male,female,both',
                // 'has_available_appointments' => 'nullable|boolean', // حذف اعتبارسنجی بولین
                'service_id'                 => 'nullable|integer|exists:services,id',
                'insurance_id'               => 'nullable|integer|exists:insurances,id',
                'limit'                      => 'nullable|integer|min:1|max:100',
                'page'                       => 'nullable|integer|min:1',
                'sort'                       => 'nullable|in:rating_desc,views_desc,appointment_soonest,successful_appointments_desc,appointment_asc',
                'service_type'               => 'nullable|in:in_person,phone,text,video',
            ]);

            $provinceId               = $request->input('province_id');
            $specialtyId              = $request->input('specialty_id');
            $gender                   = $request->input('sex');
            $hasAvailableAppointments = filter_var($request->input('has_available_appointments', false), FILTER_VALIDATE_BOOLEAN);
            $serviceId                = $request->input('service_id');
            $insuranceId              = $request->input('insurance_id');
            $limit                    = $request->input('limit', 50);
            $page                     = $request->input('page', 1);
            $sort                     = $request->input('sort', 'appointment_asc');
            $serviceType              = $request->input('service_type', 'in_person');

            // اعتبارسنجی پارامترها
            $validServiceTypes = ['in_person', 'phone', 'text', 'video'];
            $validSorts        = ['rating_desc', 'views_desc', 'appointment_soonest', 'successful_appointments_desc', 'appointment_asc'];

            if (! in_array($serviceType, $validServiceTypes)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'نوع خدمت نامعتبر است. مقادیر مجاز: in_person, phone, text, video',
                    'data'    => null,
                ], 400);
            }

            if (! in_array($sort, $validSorts)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'نوع مرتب‌سازی نامعتبر است. مقادیر مجاز: rating_desc, views_desc, appointment_soonest, successful_appointments_desc, appointment_asc',
                    'data'    => null,
                ], 400);
            }

            $cacheKey = "doctors_list_{$provinceId}_{$specialtyId}_{$gender}_{$hasAvailableAppointments}_" .
                ($serviceId ?? 'no_service') . "_" .
                ($insuranceId ?? 'no_insurance') .
                "_{$limit}_{$page}_{$sort}_{$serviceType}";

            $doctors = Cache::remember($cacheKey, 300, function () use ($provinceId, $specialtyId, $gender, $hasAvailableAppointments, $serviceId, $insuranceId, $limit, $page, $sort, $serviceType) {
                $today        = Carbon::today('Asia/Tehran');
                $calendarDays = 30;

                $query = Doctor::query()
                    ->where('status', true)
                    ->whereNotNull('first_name')->where('first_name', '!=', '')
                    ->whereNotNull('last_name')->where('last_name', '!=', '')
                    ->with([
                        'specialty'     => fn ($q) => $q->select('id', 'name'),
                        'province'      => fn ($q) => $q->select('id', 'name'),
                        'clinics'       => fn ($q) => $q->where('is_active', true)
                            ->with(['city' => fn ($q) => $q->select('id', 'name')])
                            ->select('clinics.id', 'clinics.doctor_id', 'clinics.address', 'clinics.province_id', 'clinics.city_id', 'clinics.is_main_clinic', 'clinics.payment_methods'),
                        'workSchedules' => fn ($q) => $q->where('is_working', true)
                            ->select('id', 'doctor_id', 'day', 'work_hours', 'appointment_settings'),
                        'appointments'  => fn ($q) => $q->where('status', 'scheduled')
                            ->select('id', 'doctor_id', 'appointment_date', 'appointment_time', 'status'),
                        'reviews'       => fn ($q) => $q->where('is_approved', true)
                            ->select('reviewable_id', 'reviewable_type', 'rating'),
                        'doctorTags'    => fn ($q) => $q->select('id', 'doctor_id', 'name', 'color', 'text_color'),
                    ]);

                // لود کردن رابطه appointmentConfig فقط برای نوبت‌های حضوری
                if ($serviceType === 'in_person') {
                    $query->with([
                        'appointmentConfig' => fn ($q) => $q->select(
                            'id',
                            'doctor_id',
                            'calendar_days',
                            'appointment_duration'
                        ),
                    ]);
                }

                // لود کردن رابطه counselingConfig برای نوبت‌های مشاوره
                if (in_array($serviceType, ['phone', 'text', 'video'])) {
                    $query->with([
                        'counselingConfig' => fn ($q) => $q->select(
                            'id',
                            'doctor_id',
                            'online_consultation',
                            'has_phone_counseling',
                            'has_text_counseling',
                            'has_video_counseling',
                            'calendar_days',
                            'appointment_duration',
                            'active'
                        ),
                    ]);
                }

                // فیلتر کردن بر اساس province و specialty
                if ($provinceId) {
                    $query->where('province_id', $provinceId);
                }
                if ($specialtyId) {
                    $query->where('specialty_id', $specialtyId);
                }

                // فیلتر کردن بر اساس جنسیت
                if ($gender && $gender !== 'both') {
                    $query->where('sex', $gender);
                }

                // فیلتر کردن بر اساس نوبت باز
                if ($hasAvailableAppointments) {
                    $query->whereHas('appointments', function ($q) use ($today, $calendarDays) {
                        $q->where('status', 'scheduled')
                            ->where('appointment_date', '>=', $today->toDateString())
                            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString());
                    })->whereHas('workSchedules', function ($q) use ($today, $calendarDays) {
                        $q->where('is_working', true);
                    });
                }

                // فیلتر کردن بر اساس خدمات
                if ($serviceId) {
                    $query->whereHas('services', function ($q) use ($serviceId) {
                        $q->where('services.id', $serviceId);
                    });
                }

                // فیلتر کردن بر اساس بیمه
                if ($insuranceId) {
                    $query->whereHas('insurances', function ($q) use ($insuranceId) {
                        $q->where('insurances.id', $insuranceId);
                    });
                }

                // فیلتر کردن بر اساس نوع خدمت (مشاوره)
                if ($serviceType && $serviceType !== 'in_person') {
                    $query->whereHas('counselingConfig', function ($q) use ($serviceType) {
                        $q->where('online_consultation', true)
                            ->where('active', true); // فقط رکوردهای فعال
                        if ($serviceType === 'phone') {
                            $q->where('has_phone_counseling', true);
                        } elseif ($serviceType === 'text') {
                            $q->where('has_text_counseling', true);
                        } elseif ($serviceType === 'video') {
                            $q->where('has_video_counseling', true);
                        }
                    });
                }

                // مرتب‌سازی
                switch ($sort) {
                    case 'rating_desc':
                        $query->withAvg('reviews as avg_rating', 'rating')->orderBy('avg_rating', 'desc');
                        break;
                    case 'views_desc':
                        $query->orderBy('views_count', 'desc');
                        break;
                    case 'appointment_soonest':
                        $doctors = $query->paginate($limit, ['*'], 'page', $page);
                        $doctors->getCollection()->sortBy(function ($doctor) use ($serviceType) {
                            $slotData = $this->getNextAvailableSlot($doctor, $serviceType);
                            return !empty($slotData['next_available_slot_gregorian']) ? Carbon::parse($slotData['next_available_slot_gregorian'])->timestamp : PHP_INT_MAX;
                        });
                        return $doctors;
                    case 'successful_appointments_desc':
                        $query->withCount(['appointments as successful_appointments_count' => function ($q) {
                            $q->where('status', 'completed');
                        }])->orderBy('successful_appointments_count', 'desc');
                        break;
                    case 'appointment_asc':
                        $query->orderBy('id', 'asc');
                        break;
                    default:
                        $query->orderBy('id', 'asc');
                        break;
                }

                return $query->paginate($limit, ['*'], 'page', $page);
            });

            $formattedDoctors = $doctors->map(function ($doctor) use ($serviceType) {
                $mainClinic        = $doctor->clinics->where('is_main_clinic', true)->first() ?? $doctor->clinics->first();
                $otherClinicsCount = $doctor->clinics->count() - 1;
                $city              = $mainClinic && $mainClinic->city ? $mainClinic->city->name : ($doctor->city ? $doctor->city->name : 'نامشخص');

                $slotData = $this->getNextAvailableSlot($doctor, $serviceType);

                $tags = $doctor->doctorTags->map(function ($tag) {
                    return [
                        'name'       => $tag->name,
                        'color'      => $tag->color,
                        'text_color' => $tag->text_color,
                    ];
                })->toArray();

                if ($slotData['max_appointments'] > 0 && ! in_array('کمترین معطلی', array_column($tags, 'name'))) {
                    $tags[] = [
                        'name'       => 'کمترین معطلی',
                        'color'      => 'green-100',
                        'text_color' => 'green-700',
                    ];
                }

                $services = ['نوبت‌دهی مطب'];
                if ($doctor->counselingConfig && $doctor->counselingConfig->online_consultation && $doctor->counselingConfig->active) {
                    if ($doctor->counselingConfig->has_phone_counseling) {
                        $services[] = 'مشاوره تلفنی';
                    }
                    if ($doctor->counselingConfig->has_text_counseling) {
                        $services[] = 'مشاوره متنی';
                    }
                    if ($doctor->counselingConfig->has_video_counseling) {
                        $services[] = 'مشاوره ویدیویی';
                    }
                }

                $rating       = $doctor->reviews->avg('rating') ?: 0;
                $reviewsCount = $doctor->reviews->count();
                $viewsCount   = $doctor->views_count ?? 0;

                return [
                    'id'                  => $doctor->id,
                    'name'                => $doctor->display_name ?? ($doctor->first_name . ' ' . $doctor->last_name),
                    'specialty'           => $doctor->specialty?->name,
                    'avatar'              => $doctor->profile_photo_path ? asset('storage/' . $doctor->profile_photo_path) : '/default-avatar.png',
                    'location'            => [
                        'province'            => $doctor->province?->name ?? 'نامشخص',
                        'city'                => $city,
                        'address'             => $mainClinic?->address ?? 'نامشخص',
                        'other_clinics_count' => $otherClinicsCount > 0 ? $otherClinicsCount : 0,
                    ],
                    'rating'              => round($rating, 1),
                    'reviews_count'       => $reviewsCount,
                    'views_count'         => $viewsCount,
                    'next_available_slot' => $slotData['next_available_slot'] ?? 'نوبت خالی ندارد',
                    'tags'                => $tags,
                    'services'            => $services,
                    'profile_url'         => "/profile/doctor/{$doctor->slug}",
                    'appointment_url'     => "/api/appointments/book/{$doctor->id}",
                    'consultation_url'    => "/api/consultations/book/{$doctor->id}",
                ];
            });

            return response()->json([
                'status'     => 'success',
                'data'       => $formattedDoctors,
                'pagination' => [
                    'total'        => $doctors->total(),
                    'per_page'     => $doctors->perPage(),
                    'current_page' => $doctors->currentPage(),
                    'last_page'    => $doctors->lastPage(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('GetDoctors - Error: ' . $e->getMessage(), [
                'request'   => $request->all(),
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    private function getNextAvailableSlot($doctor, $clinicId)
    {
        $doctorId        = $doctor->id;
        $today           = Carbon::today('Asia/Tehran');
        $now             = Carbon::now('Asia/Tehran');
        $daysOfWeek      = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        $appointmentConfig = $doctor->appointmentConfig;
        $calendarDays      = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;
        $duration          = $appointmentConfig ? ($appointmentConfig->appointment_duration ?? 15) : 15;

        $schedules = $doctor->workSchedules;
        if ($schedules->isEmpty()) {
            return ['next_available_slot' => null, 'slots' => [], 'max_appointments' => 0];
        }

        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where('status', 'scheduled')
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get()
            ->groupBy('appointment_date');

        $slots             = [];
        $nextAvailableSlot = null;
        $nextAvailableSlotGregorian = null;

        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex  = ($currentDayIndex + $i) % 7;
            $dayName        = $daysOfWeek[$checkDayIndex];
            $checkDate      = $today->copy()->addDays($i);
            $jalaliDate     = Jalalian::fromCarbon($checkDate)->format('j F Y');
            $persianDayName = Jalalian::fromCarbon($checkDate)->format('l');

            $dayAppointments = $bookedAppointments->get($checkDate->toDateString(), collect());
            $activeSlots     = [];
            $inactiveSlots   = [];

            foreach ($schedules as $schedule) {
                if ($schedule->day !== $dayName) {
                    continue;
                }

                $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                if (! is_array($workHours) || empty($workHours)) {
                    continue;
                }
                $workHour = $workHours[0];

                $startTime = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime   = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['end'], 'Asia/Tehran');

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $nextTime = (clone $currentTime)->addMinutes($duration);
                    $slotTime = $currentTime->format('H:i');

                    $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
                        $apptStart = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time, 'Asia/Tehran');
                        $apptEnd   = (clone $apptStart)->addMinutes($duration);
                        return $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);
                    });

                    if ($checkDate->isToday()) {
                        if ($isBooked || $currentTime->lt($now)) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (! $nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                                $nextAvailableSlotGregorian = $checkDate->toDateString() . ' ' . $slotTime;
                            }
                        }
                    } else {
                        if (! $isBooked) {
                            $activeSlots[] = $slotTime;
                            if (! $nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                                $nextAvailableSlotGregorian = $checkDate->toDateString() . ' ' . $slotTime;
                            }
                        }
                    }

                    $currentTime->addMinutes($duration);
                }
            }

            if (! empty($activeSlots) || ! empty($inactiveSlots)) {
                $slotData = [
                    'date'            => $jalaliDate,
                    'day_name'        => $persianDayName,
                    'available_slots' => $activeSlots,
                    'available_count' => count($activeSlots),
                ];
                if ($checkDate->isToday()) {
                    $slotData['inactive_slots'] = $inactiveSlots;
                    $slotData['inactive_count'] = count($inactiveSlots);
                }
                $slots[] = $slotData;
            }
        }

        return [
            'next_available_slot' => $nextAvailableSlot,
            'next_available_slot_gregorian' => $nextAvailableSlotGregorian,
            'slots'               => $slots,
            'max_appointments'    => $schedules->first()->appointment_settings[0]['max_appointments'] ?? 22,
        ];
    }
}
