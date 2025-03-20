<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

class DoctorListingController extends Controller
{
    /**
     * گرفتن لیست پزشکان برای صفحه اصلی
     *
     * @queryParam province_id integer فیلتر بر اساس شناسه استان (اختیاری)
     * @queryParam specialty_id integer فیلتر بر اساس شناسه تخصص (اختیاری)
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، پیش‌فرض: 10)
     * @queryParam page integer شماره صفحه (اختیاری، پیش‌فرض: 1)
     * @queryParam sort string مرتب‌سازی (مثال: "rating_desc", "views_desc", "appointment_asc") (اختیاری)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "دکتر محمود عطایی",
     *       "specialty": "فوق تخصص قلب و عروق",
     *       "avatar": "https://example.com/avatar.png",
     *       "location": {
     *         "city": "تهران",
     *         "address": "میدان تجریش، پایین‌تر از مترو، کوچه طالقانی، ساختمان پزشکان، واحد 120",
     *         "other_clinics_count": 2
     *       },
     *       "rating": 4.3,
     *       "reviews_count": 189,
     *       "views_count": 54000,
     *       "next_available_slot": "۵ آذر ۱۴۰۳ ساعت ۱۷:۳۰",
     *       "tags": ["کمترین معطلی", "خوش برخورد", "پوشش بیمه"],
     *       "services": [
     *         "نوبت‌دهی مطب",
     *         "مشاوره تلفنی",
     *         "مشاوره متنی"
     *       ],
     *       "profile_url": "/profile/doctor/dr-mahmoud-ataei",
     *       "appointment_url": "/api/appointments/book/1",
     *       "consultation_url": "/api/consultations/book/1"
     *     }
     *   ],
     *   "pagination": {
     *     "total": 50,
     *     "per_page": 10,
     *     "current_page": 1,
     *     "last_page": 5
     *   }
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
            // پارامترهای ورودی
            $provinceId  = $request->input('province_id');
            $specialtyId = $request->input('specialty_id');
            $limit       = $request->input('limit', 10);
            $page        = $request->input('page', 1);
            $sort        = $request->input('sort', 'rating_desc');

            $cacheKey = "doctors_list_{$provinceId}_{$specialtyId}_{$limit}_{$page}_{$sort}";

            $doctors = Cache::remember($cacheKey, 300, function () use ($provinceId, $specialtyId, $limit, $page, $sort) {
                $query = Doctor::query()
                    ->where('is_active', true)
                    ->where('is_verified', true)
                    ->with([
                        'specialty'     => fn($q)     => $q->select('id', 'name'),
                        'province'      => fn($q)      => $q->select('id', 'name'),
                        'clinics'       => fn($q)       => $q->where('is_active', true)
                            ->with(['city' => fn($q) => $q->select('id', 'name')])
                            ->select('id', 'doctor_id', 'address', 'province_id', 'city_id'),
                        'workSchedules' => fn($q) => $q->where('is_working', true),
                        'appointmentConfig',
                        'appointments'  => fn($q)  => $q->where('status', 'scheduled'),
                        'reviews'       => fn($q)       => $q->where('is_approved', true)->select('reviewable_id', 'reviewable_type', 'rating'),
                    ]);

                if ($provinceId) {
                    $query->where('province_id', $provinceId);
                }
                if ($specialtyId) {
                    $query->where('specialty_id', $specialtyId);
                }

                switch ($sort) {
                    case 'rating_desc':
                        $query->withAvg('reviews as avg_rating', 'rating')->orderBy('avg_rating', 'desc');
                        break;
                    case 'views_desc':
                        $query->orderBy('views_count', 'desc');
                        break;
                    case 'appointment_asc':
                        $query->orderBy('next_available_slot', 'asc'); // نیاز به محاسبه داره
                        break;
                    default:
                        $query->orderBy('id', 'desc');
                }

                return $query->paginate($limit, ['*'], 'page', $page);
            });

            $formattedDoctors = $doctors->map(function ($doctor) {
                $mainClinic        = $doctor->clinics->where('is_main_clinic', true)->first() ?? $doctor->clinics->first();
                $otherClinicsCount = $doctor->clinics->count() - 1;
                $city              = $mainClinic && $mainClinic->city ? $mainClinic->city->name : ($doctor->city ? $doctor->city->name : 'نامشخص');

                $slotData   = $this->getNextAvailableSlot($doctor);
                $jalaliDate = $slotData['next_available_slot']
                ? Jalalian::fromCarbon(Carbon::parse($slotData['next_available_slot']))->format('j F Y ساعت H:i')
                : null;

                $tags = [];
                if ($slotData['max_appointments'] > 0) {
                    $tags[] = 'کمترین معطلی';
                }

                $tags[] = 'خوش برخورد'; // فرضیه
                if ($doctor->clinics->pluck('payment_methods')->contains('online')) {
                    $tags[] = 'پوشش بیمه';
                }

                $services = ['نوبت‌دهی مطب'];
                if ($doctor->appointmentConfig && $doctor->appointmentConfig->online_consultation) {
                    $services[] = 'مشاوره تلفنی';
                    $services[] = 'مشاوره متنی';
                }

                                                                      // محاسبه امتیاز و تعداد نظرات از reviews
                $rating       = $doctor->reviews->avg('rating') ?: 0; // میانگین امتیاز
                $reviewsCount = $doctor->reviews->count();            // تعداد نظرات تأییدشده
                $viewsCount   = $doctor->views_count ?? 0;            // تعداد بازدید از doctors

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
                    'rating'              => round($rating, 1), // گرد کردن به 1 رقم اعشار
                    'reviews_count'       => $reviewsCount,
                    'views_count'         => $viewsCount,
                    'next_available_slot' => $jalaliDate,
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
            Log::error('GetDoctors - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * محاسبه اولین نوبت خالی برای یک پزشک
     */
   private function getNextAvailableSlot($doctor)
    {
        $doctorId = $doctor->id;
        $today = Carbon::today();
        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        $appointmentConfig = Cache::remember("appointment_config_{$doctorId}", 3600, fn() => $doctor->appointmentConfig);
        $calendarDays = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;

        $schedules = Cache::remember("work_schedules_{$doctorId}", 3600, fn() => $doctor->workSchedules);
        if ($schedules->isEmpty()) {
            return ['next_available_slot' => null, 'max_appointments' => 0];
        }

        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('status', 'scheduled')
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get()
            ->groupBy('appointment_date');

        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $dayName = $daysOfWeek[$checkDayIndex];
            $checkDate = $today->copy()->addDays($i);

            $dayAppointments = $bookedAppointments->get($checkDate->toDateString(), collect());

            foreach ($schedules as $schedule) {
                $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                if (!is_array($workHours)) continue;

                foreach ($workHours as $workHour) {
                    $startTime = (clone $checkDate)->setTimeFromTimeString($workHour['start']);
                    $endTime = (clone $checkDate)->setTimeFromTimeString($workHour['end']);

                    $appointmentSettings = is_string($schedule->appointment_settings) ? json_decode($schedule->appointment_settings, true) : $schedule->appointment_settings;
                    if (!is_array($appointmentSettings)) continue;

                    foreach ($appointmentSettings as $setting) {
                        if ($setting['selected_day'] !== $dayName) continue;

                        $maxAppointments = $setting['max_appointments'] ?? $workHour['max_appointments'] ?? 10;
                        $duration = $appointmentConfig->appointment_duration ?? 30;

                        $currentTime = $startTime;
                        $appointmentsBooked = $dayAppointments->count();

                        while ($currentTime < $endTime && $appointmentsBooked < $maxAppointments) {
                            $isBooked = $dayAppointments->contains(fn($appt) => Carbon::parse($appt->start_time)->eq($currentTime));

                            if (!$isBooked) {
                                return [
                                    'next_available_slot' => $currentTime->toIso8601String(),
                                    'max_appointments' => $maxAppointments,
                                ];
                            }

                            $currentTime->addMinutes($duration);
                            $appointmentsBooked++;
                        }
                    }
                }
            }
        }

        return ['next_available_slot' => null, 'max_appointments' => 0];
    }
}
