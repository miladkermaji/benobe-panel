<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Zone;
use App\Models\Doctor;
use App\Models\Service;
use App\Models\Insurance;
use App\Models\Specialty;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class DoctorListingController extends Controller
{
    /**
 * دریافت لیست پزشکان با فیلترهای مختلف
 * @param string|null $province_slug اسلاگ استان
 * @param string|null $city_slug اسلاگ شهر
 * @param string|null $specialty_slug اسلاگ تخصص
 * @param string|null $insurance_slug اسلاگ بیمه
 * @param string|null $service_slug اسلاگ خدمت
 * @response 200 {
 *   "status": "success",
 *   "data": [
 *     {
 *       "id": 1,
 *       "name": "دکتر نمونه",
 *       "slug": "دکتر-نمونه",
 *       "specialty": {
 *         "id": 1,
 *         "name": "متخصص قلب",
 *         "slug": "متخصص-قلب"
 *       },
 *       "avatar": "/storage/avatars/doctor.jpg",
 *       "location": {
 *         "province": {
 *           "id": 1,
 *           "name": "تهران",
 *           "slug": "تهران"
 *         },
 *         "city": {
 *           "name": "تهران",
 *           "slug": "تهران"
 *         },
 *         "address": "خیابان نمونه",
 *         "clinic": {
 *           "id": 1,
 *           "name": "کلینیک نمونه",
 *           "slug": "کلینیک-نمونه"
 *         },
 *         "other_clinics_count": 1
 *       },
 *       "rating": 4.5,
 *       "reviews_count": 10,
 *       "views_count": 100,
 *       "next_available_slot": "جمعه ۱۷ مرداد ساعت ۱۴:۳۰",
 *       "tags": [
 *         {
 *           "name": "کمترین معطلی",
 *           "color": "green-100",
 *           "text_color": "green-700"
 *         }
 *       ],
 *       "services": [
 *         {
 *           "id": 1,
 *           "name": "نوبت‌دهی مطب",
 *           "slug": "نوبت-دهی-مطب"
 *         },
 *         {
 *           "id": 2,
 *           "name": "مشاوره تلفنی",
 *           "slug": "مشاوره-تلفنی"
 *         }
 *       ],
 *       "insurances": [
 *         {
 *           "id": 1,
 *           "name": "بیمه تامین اجتماعی",
 *           "slug": "بیمه-تامین-اجتماعی"
 *         }
 *       ],
 *       "profile_url": "/profile/doctor/دکتر-نمونه",
 *       "appointment_url": "/api/appointments/book/دکتر-نمونه",
 *       "consultation_url": "/api/consultations/book/دکتر-نمونه"
 *     }
 *   ],
 *   "pagination": {
 *     "total": 100,
 *     "per_page": 50,
 *     "current_page": 1,
 *     "last_page": 2
 *   }
 * }
 * @response 400 {
 *   "status": "error",
 *   "message": "نوع خدمت یا مرتب‌سازی نامعتبر است.",
 *   "data": null
 * }
 * @response 404 {
 *   "status": "error",
 *   "message": "استان، شهر، تخصص، بیمه یا خدمت یافت نشد.",
 *   "data": null
 * }
 * @response 422 {
 *   "status": "error",
 *   "message": "خطای اعتبارسنجی ورودی‌ها",
 *   "errors": {},
 *   "data": null
 * }
 * @response 500 {
 *   "status": "error",
 *   "message": "خطای سرور",
 *   "data": null
 * }
 */
    public function getDoctors(Request $request)
    {
        try {
            // اعتبارسنجی ورودی‌ها
            $validated = $request->validate([
                'province_slug'              => 'nullable|exists:zone,slug',
                'city_slug'                  => 'nullable|exists:zone,slug',
                'specialty_slug'             => 'nullable|exists:specialties,slug',
                'sex'                        => 'nullable|in:male,female,both',
                'has_available_appointments' => 'nullable|string|in:true,false,1,0',
                'service_slug'               => 'nullable|exists:services,slug',
                'insurance_slug'             => 'nullable|exists:insurances,slug',
                'limit'                      => 'nullable|integer|min:1|max:100',
                'page'                       => 'nullable|integer|min:1',
                'sort'                       => 'nullable|in:rating_desc,views_desc,appointment_soonest,successful_appointments_desc,appointment_asc',
                'service_type'               => 'nullable|in:in_person,phone,text,video',
            ], [
                'province_slug.exists'         => 'استان انتخاب‌شده وجود ندارد.',
                'city_slug.exists'             => 'شهر انتخاب‌شده وجود ندارد.',
                'specialty_slug.exists'        => 'تخصص انتخاب‌شده وجود ندارد.',
                'sex.in'                       => 'جنسیت باید یکی از مقادیر male, female, both باشد.',
                'has_available_appointments.in' => 'مقدار نوبت‌های در دسترس باید true, false, 1 یا 0 باشد.',
                'service_slug.exists'          => 'خدمت انتخاب‌شده وجود ندارد.',
                'insurance_slug.exists'        => 'بیمه انتخاب‌شده وجود ندارد.',
                'limit.integer'                => 'محدودیت باید یک عدد صحیح باشد.',
                'limit.min'                    => 'محدودیت باید حداقل 1 باشد.',
                'limit.max'                    => 'محدودیت نمی‌تواند بیشتر از 100 باشد.',
                'page.integer'                 => 'شماره صفحه باید یک عدد صحیح باشد.',
                'page.min'                     => 'شماره صفحه باید حداقل 1 باشد.',
                'sort.in'                      => 'نوع مرتب‌سازی باید یکی از مقادیر rating_desc, views_desc, appointment_soonest, successful_appointments_desc, appointment_asc باشد.',
                'service_type.in'              => 'نوع خدمت باید یکی از مقادیر in_person, phone, text, video باشد.',
            ]);

            $provinceSlug             = $request->input('province_slug');
            $citySlug                 = $request->input('city_slug');
            $specialtySlug            = $request->input('specialty_slug');
            $gender                   = $request->input('sex');
            $hasAvailableAppointments = filter_var($request->input('has_available_appointments', false), FILTER_VALIDATE_BOOLEAN);
            $serviceSlug              = $request->input('service_slug');
            $insuranceSlug            = $request->input('insurance_slug');
            $limit                    = $request->input('limit', 50);
            $page                     = $request->input('page', 1);
            $sort                     = $request->input('sort', 'appointment_asc');
            $serviceType              = $request->input('service_type', 'in_person');

            // اعتبارسنجی پارامترها
            $validServiceTypes = ['in_person', 'phone', 'text', 'video'];
            $validSorts        = ['rating_desc', 'views_desc', 'appointment_soonest', 'successful_appointments_desc', 'appointment_asc'];

            if (!in_array($serviceType, $validServiceTypes)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'نوع خدمت نامعتبر است. مقادیر مجاز: in_person, phone, text, video',
                    'data'    => null,
                ], 400);
            }

            if (!in_array($sort, $validSorts)) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'نوع مرتب‌سازی نامعتبر است. مقادیر مجاز: rating_desc, views_desc, appointment_soonest, successful_appointments_desc, appointment_asc',
                    'data'    => null,
                ], 400);
            }

            // پیدا کردن province_id از province_slug
            $provinceId = null;
            if ($provinceSlug) {
                $province = Zone::where('level', 1)->where('slug', $provinceSlug)->first();
                if (!$province) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'استان یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
                $provinceId = $province->id;

                // Debug log
                Log::info('Province filter debug', [
                    'province_slug' => $provinceSlug,
                    'province_id' => $provinceId,
                    'province_name' => $province->name
                ]);
            }

            // پیدا کردن city_id از city_slug
            $cityId = null;
            if ($citySlug) {
                $city = Zone::where('level', 2)->where('slug', $citySlug)->first();
                if (!$city) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'شهر یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
                $cityId = $city->id;
            }

            // پیدا کردن specialty_id از specialty_slug
            $specialtyId = null;
            if ($specialtySlug) {
                $specialty = Specialty::where('slug', $specialtySlug)->first();
                if (!$specialty) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'تخصص یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
                $specialtyId = $specialty->id;
            }

            // پیدا کردن service_id از service_slug
            $serviceId = null;
            if ($serviceSlug) {
                $service = Service::where('slug', $serviceSlug)->first();
                if (!$service) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'خدمت یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
                $serviceId = $service->id;
            }

            // پیدا کردن insurance_id از insurance_slug
            $insuranceId = null;
            if ($insuranceSlug) {
                $insurance = Insurance::where('slug', $insuranceSlug)->first();
                if (!$insurance) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'بیمه یافت نشد.',
                        'data'    => null,
                    ], 404);
                }
                $insuranceId = $insurance->id;
            }

            $cacheKey = "doctors_list_{$provinceSlug}_{$citySlug}_{$specialtySlug}_{$gender}_{$hasAvailableAppointments}_{$serviceSlug}_{$insuranceSlug}_{$limit}_{$page}_{$sort}_{$serviceType}";

            $doctors = Cache::remember($cacheKey, 300, function () use ($provinceId, $cityId, $specialtyId, $gender, $hasAvailableAppointments, $serviceId, $insuranceId, $limit, $page, $sort, $serviceType) {
                $today        = Carbon::today('Asia/Tehran');
                $calendarDays = 30;

                $query = Doctor::query()
                    ->where('status', true)
                    ->whereNotNull('first_name')->where('first_name', '!=', '')
                    ->whereNotNull('last_name')->where('last_name', '!=', '')
                    ->with([
                        'specialty'     => fn ($q) => $q->select('id', 'name', 'slug'),
                        'province'      => fn ($q) => $q->select('id', 'name', 'slug'),
                        'city'          => fn ($q) => $q->select('id', 'name', 'slug'),
                        'clinics'       => fn ($q) => $q->where('is_active', true)
                            ->with(['city' => fn ($q) => $q->select('id', 'name', 'slug')])
                            ->select('medical_centers.id', 'medical_centers.name', 'medical_centers.slug', 'medical_centers.address', 'medical_centers.province_id', 'medical_centers.city_id', 'medical_centers.payment_methods', 'medical_centers.is_main_center'),
                        'workSchedules' => fn ($q) => $q->where('is_working', true)
                            ->select('id', 'doctor_id', 'day', 'work_hours', 'appointment_settings'),
                        'appointments'  => fn ($q) => $q->where('status', 'scheduled')
                            ->select('id', 'doctor_id', 'appointment_date', 'appointment_time', 'status'),
                        'reviews'       => fn ($q) => $q->where('is_approved', true)
                            ->select('reviewable_id', 'reviewable_type', 'rating'),
                        'doctorTags'    => fn ($q) => $q->select('id', 'doctor_id', 'name', 'color', 'text_color'),
                        'insurances'    => fn ($q) => $q->select('insurances.id', 'insurances.name', 'insurances.slug'),
                        'services'      => fn ($q) => $q->select('services.id', 'services.name', 'services.slug'),
                    ]);

                // Debug: Count total doctors before filters
                $totalDoctorsBeforeFilters = $query->count();
                Log::info('Total doctors before filters', ['count' => $totalDoctorsBeforeFilters]);

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

                // فیلتر کردن بر اساس province و city
                if ($provinceId) {
                    $query->where(function ($q) use ($provinceId) {
                        $q->where('province_id', $provinceId)
                          ->orWhereHas('clinics', function ($clinicQuery) use ($provinceId) {
                              $clinicQuery->where('province_id', $provinceId);
                          });
                    });

                    // Debug: Count after province filter
                    $doctorsAfterProvinceFilter = (clone $query)->count();
                    Log::info('Doctors after province filter', [
                        'province_id' => $provinceId,
                        'count' => $doctorsAfterProvinceFilter
                    ]);

                    // Debug: Check doctors with this province_id directly
                    $doctorsWithProvinceId = Doctor::where('province_id', $provinceId)->count();
                    Log::info('Doctors with province_id directly', [
                        'province_id' => $provinceId,
                        'count' => $doctorsWithProvinceId
                    ]);

                    // Debug: Check clinics with this province_id
                    $clinicsWithProvinceId = \App\Models\MedicalCenter::where('province_id', $provinceId)->count();
                    Log::info('Clinics with province_id', [
                        'province_id' => $provinceId,
                        'count' => $clinicsWithProvinceId
                    ]);
                }
                if ($cityId) {
                    $query->where(function ($q) use ($cityId) {
                        $q->where('city_id', $cityId)
                          ->orWhereHas('clinics', function ($clinicQuery) use ($cityId) {
                              $clinicQuery->where('city_id', $cityId);
                          });
                    });

                    // Debug: Count after city filter
                    $doctorsAfterCityFilter = (clone $query)->count();
                    Log::info('Doctors after city filter', [
                        'city_id' => $cityId,
                        'count' => $doctorsAfterCityFilter
                    ]);
                }

                // فیلتر کردن بر اساس تخصص
                if ($specialtyId) {
                    $query->where('specialty_id', $specialtyId);
                }

                // فیلتر کردن بر اساس جنسیت
                if ($gender && $gender !== 'both') {
                    $query->where('sex', $gender);
                }

                // فیلتر کردن بر اساس نوبت باز
                if ($hasAvailableAppointments) {
                    $query->whereHas('workSchedules', function ($q) {
                        $q->where('is_working', true);
                    })->where(function ($q) use ($today, $calendarDays) {
                        $q->whereHas('workSchedules', function ($scheduleQuery) {
                            $scheduleQuery->where('is_working', true);
                        });
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
                          ->where('active', true);
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
                        $doctors->getCollection()->sortBy(function ($doctor) {
                            $mainClinic = $doctor->clinics->where('is_main_center', true)->first() ?? $doctor->clinics->first();
                            $clinicId = $mainClinic ? $mainClinic->id : null;
                            $slotData = $this->getNextAvailableSlot($doctor, $clinicId);
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
                $mainClinic        = $doctor->clinics->where('is_main_center', true)->first() ?? $doctor->clinics->first();
                $otherClinicsCount = $doctor->clinics->count() - 1;
                $city              = $mainClinic && $mainClinic->city ? $mainClinic->city->name : ($doctor->city ? $doctor->city->name : 'نامشخص');
                $citySlug          = $mainClinic && $mainClinic->city ? $mainClinic->city->slug : ($doctor->city ? $doctor->city->slug : null);

                $clinicId = $mainClinic ? $mainClinic->id : null;
                $slotData = $this->getNextAvailableSlot($doctor, $clinicId);

                $tags = $doctor->doctorTags->map(function ($tag) {
                    return [
                        'name'       => $tag->name,
                        'color'      => $tag->color,
                        'text_color' => $tag->text_color,
                    ];
                })->toArray();

                if ($slotData['max_appointments'] > 0 && !in_array('کمترین معطلی', array_column($tags, 'name'))) {
                    $tags[] = [
                        'name'       => 'کمترین معطلی',
                        'color'      => 'green-100',
                        'text_color' => 'green-700',
                    ];
                }

                $rating       = $doctor->reviews->avg('rating') ?: 0;
                $reviewsCount = $doctor->reviews->count();
                $viewsCount   = $doctor->views_count ?? 0;

                return [
                    'id'                  => $doctor->id,
                    'name'                => $doctor->display_name ?? ($doctor->first_name . ' ' . $doctor->last_name),
                    'slug'                => $doctor->slug,
                    'specialty'           => [
                        'id'   => $doctor->specialty?->id,
                        'name' => $doctor->specialty?->name,
                        'slug' => $doctor->specialty?->slug,
                    ],
                    'avatar'              => $doctor->profile_photo_path ? Storage::url($doctor->profile_photo_path) : '/default-avatar.png',
                    'location'            => [
                        'province'            => [
                            'id'   => $doctor->province?->id,
                            'name' => $doctor->province?->name,
                            'slug' => $doctor->province?->slug,
                        ],
                        'city'                => [
                            'name' => $city,
                            'slug' => $citySlug,
                        ],
                        'address'             => $mainClinic?->address ?? 'نامشخص',
                        'clinic'              => $mainClinic ? [
                            'id'   => $mainClinic->id,
                            'name' => $mainClinic->name,
                            'slug' => $mainClinic->slug,
                        ] : null,
                        'other_clinics_count' => $otherClinicsCount > 0 ? $otherClinicsCount : 0,
                    ],
                    'rating'              => round($rating, 1),
                    'reviews_count'       => $reviewsCount,
                    'views_count'         => $viewsCount,
                    'next_available_slot' => $slotData['next_available_slot'] ?? 'نوبت خالی ندارد',
                    'tags'                => $tags,
                    'services'            => $doctor->services->map(function ($service) {
                        return [
                            'id'   => $service->id,
                            'name' => $service->name,
                            'slug' => $service->slug,
                        ];
                    })->toArray(),
                    'insurances'          => $doctor->insurances->map(function ($insurance) {
                        return [
                            'id'   => $insurance->id,
                            'name' => $insurance->name,
                            'slug' => $insurance->slug,
                        ];
                    })->toArray(),
                    'profile_url'         => "/profile/doctor/{$doctor->slug}",
                    'appointment_url'     => "/api/appointments/book/{$doctor->slug}",
                    'consultation_url'    => "/api/consultations/book/{$doctor->slug}",
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
                'filters'    => [
                    'province_slug' => $provinceSlug,
                    'city_slug'     => $citySlug,
                    'specialty_slug' => $specialtySlug,
                    'total_doctors_before_filters' => $totalDoctorsBeforeFilters ?? 0,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای اعتبارسنجی ورودی‌ها',
                'errors'  => $e->errors(),
                'data'    => null,
            ], 422);
        } catch (\Exception $e) {
            Log::error('Doctor listing error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور: ' . $e->getMessage(),
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
        $defaultDuration   = $appointmentConfig ? ($appointmentConfig->appointment_duration ?? 15) : 15;

        $schedules = $doctor->workSchedules;
        if ($schedules->isEmpty()) {
            return ['next_available_slot' => null, 'slots' => [], 'max_appointments' => 0];
        }

        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->when($clinicId, function ($query) use ($clinicId) {
                return $query->where('medical_center_id', $clinicId);
            })
            ->where('status', 'scheduled')
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get()
            ->groupBy('appointment_date');

        $slots             = [];
        $nextAvailableSlot = null;
        $nextAvailableSlotGregorian = null;

        // فیکس کردن مشکل appointment_settings - اطمینان از decode شدن
        $firstSchedule = $schedules->first();
        $appointmentSettings = $firstSchedule ? (
            is_string($firstSchedule->appointment_settings)
                ? json_decode($firstSchedule->appointment_settings, true)
                : $firstSchedule->appointment_settings
        ) : [];
        $maxAppointments = $this->getMaxAppointmentsFromSettings($appointmentSettings);

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

                // اطمینان از اینکه work_hour معتبر است
                if (!isset($workHour['start']) || !isset($workHour['end']) ||
                    empty($workHour['start']) || empty($workHour['end']) ||
                    $workHour['start'] === 'null' || $workHour['end'] === 'null') {
                    continue;
                }

                // استفاده از تابع کمکی برای استخراج duration
                $appointmentSettings = is_string($schedule->appointment_settings) ? json_decode($schedule->appointment_settings, true) : $schedule->appointment_settings;
                $duration = $this->getAppointmentDuration($appointmentSettings, $dayName, $defaultDuration);

                $startTime = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime   = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['end'], 'Asia/Tehran');

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $nextTime = (clone $currentTime)->addMinutes($duration);
                    $slotTime = $currentTime->format('H:i');

                    // اطمینان از اینکه slotTime معتبر است
                    if (empty($slotTime) || $slotTime === 'null') {
                        $currentTime->addMinutes($duration);
                        continue;
                    }

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
            'max_appointments'    => $maxAppointments,
        ];
    }

    private function hasAvailableAppointments($doctor, $clinicId = null)
    {
        $doctorId        = $doctor->id;
        $today           = Carbon::today('Asia/Tehran');
        $now             = Carbon::now('Asia/Tehran');
        $daysOfWeek      = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        $appointmentConfig = $doctor->appointmentConfig;
        $calendarDays      = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;

        $schedules = $doctor->workSchedules;
        if ($schedules->isEmpty()) {
            return false;
        }

        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->when($clinicId, function ($query) use ($clinicId) {
                return $query->where('medical_center_id', $clinicId);
            })
            ->where('status', 'scheduled')
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get()
            ->groupBy('appointment_date');

        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $dayName       = $daysOfWeek[$checkDayIndex];
            $checkDate     = $today->copy()->addDays($i);

            $dayAppointments = $bookedAppointments->get($checkDate->toDateString(), collect());

            foreach ($schedules as $schedule) {
                if ($schedule->day !== $dayName) {
                    continue;
                }

                $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                if (! is_array($workHours) || empty($workHours)) {
                    continue;
                }
                $workHour = $workHours[0];

                // اطمینان از اینکه work_hour معتبر است
                if (!isset($workHour['start']) || !isset($workHour['end']) ||
                    empty($workHour['start']) || empty($workHour['end']) ||
                    $workHour['start'] === 'null' || $workHour['end'] === 'null') {
                    continue;
                }

                $appointmentSettings = is_string($schedule->appointment_settings) ? json_decode($schedule->appointment_settings, true) : $schedule->appointment_settings;
                $duration = $this->getAppointmentDuration($appointmentSettings, $dayName, 15);

                $startTime = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime   = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['end'], 'Asia/Tehran');

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $nextTime = (clone $currentTime)->addMinutes($duration);
                    $slotTime = $currentTime->format('H:i');

                    // اطمینان از اینکه slotTime معتبر است
                    if (empty($slotTime) || $slotTime === 'null') {
                        $currentTime->addMinutes($duration);
                        continue;
                    }

                    $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
                        $apptStart = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time, 'Asia/Tehran');
                        $apptEnd   = (clone $apptStart)->addMinutes($duration);
                        return $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);
                    });

                    if ($checkDate->isToday()) {
                        if (!$isBooked && $currentTime->gt($now)) {
                            return true; // نوبت خالی پیدا شد
                        }
                    } else {
                        if (!$isBooked) {
                            return true; // نوبت خالی پیدا شد
                        }
                    }

                    $currentTime->addMinutes($duration);
                }
            }
        }

        return false; // هیچ نوبت خالی پیدا نشد
    }

    private function getAppointmentDuration($appointmentSettings, $dayOfWeek, $defaultDuration = 15)
    {
        if (empty($appointmentSettings) || !is_array($appointmentSettings)) {
            return $defaultDuration;
        }

        if (isset($appointmentSettings[0]['day'])) {
            foreach ($appointmentSettings as $setting) {
                if (is_array($setting) && isset($setting['day']) && $setting['day'] === $dayOfWeek) {
                    return $setting['appointment_duration'] ?? $defaultDuration;
                }
            }
            return $defaultDuration;
        }

        foreach ($appointmentSettings as $setting) {
            if (is_array($setting) && isset($setting['days']) && is_array($setting['days']) && in_array($dayOfWeek, $setting['days'])) {
                return $setting['appointment_duration'] ?? $defaultDuration;
            }
        }

        return $defaultDuration;
    }

    private function getMaxAppointmentsFromSettings($appointmentSettings, $defaultMaxAppointments = 22)
    {
        if (empty($appointmentSettings) || !is_array($appointmentSettings)) {
            return $defaultMaxAppointments;
        }

        if (isset($appointmentSettings[0]['day'])) {
            foreach ($appointmentSettings as $setting) {
                if (is_array($setting) && isset($setting['max_appointments']) && $setting['max_appointments'] > 0) {
                    return $setting['max_appointments'];
                }
            }
            return $defaultMaxAppointments;
        }

        foreach ($appointmentSettings as $setting) {
            if (is_array($setting) && isset($setting['max_appointments']) && $setting['max_appointments'] > 0) {
                return $setting['max_appointments'];
            }
        }

        return $defaultMaxAppointments;
    }
}
