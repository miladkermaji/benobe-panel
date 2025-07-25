<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\BestDoctor;
use App\Models\Appointment;
use Illuminate\Http\Request;
use App\Models\UserDoctorLike;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class DoctorController extends Controller
{
    /**
     * گرفتن لیست پزشکان لایک‌شده توسط کاربر
     *
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "دکتر محمدی",
     *       "specialty": "متخصص قلب و عروق",
     *       "license_number": "۱۲۳۴۵۶",
     *       "profile_photo_path": "https://example.com/photos/doctor1.jpg",
     *       "liked_at": "2025-03-16T12:00:00Z"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "status": "error",
     *   "message": "توکن نامعتبر است",
     *   "data": null
     * }
     * @response 500 {
     *   "status": "error",
     *   "message": "خطای سرور",
     *   "data": null
     * }
     */
    public function getMyDoctors(Request $request)
    {
        try {
            // گرفتن توکن از هدر یا کوکی
            $token = $request->bearerToken() ?: $request->cookie('auth_token');
            if (! $token) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن ارائه نشده است',
                    'data'    => null,
                ], 401);
            }

            // احراز هویت کاربر
            try {

                $user = Auth::user();

                if (! $user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن پزشکان لایک‌شده
            $likedDoctors = UserDoctorLike::where('likeable_id', $user->id)
                ->where('likeable_type', 'App\\Models\\User')
                ->with([
                    'doctor' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'specialty_id', 'license_number', 'profile_photo_path')
                            ->with(['specialty' => function ($query) {
                                $query->select('id', 'name');
                            }]);
                    },
                ])
                ->select('id', 'likeable_id', 'likeable_type', 'doctor_id', 'liked_at')
                ->get()
                ->pluck('doctor')
                ->filter();

            // فرمت کردن داده‌ها
            $formattedDoctors = $likedDoctors->map(function ($doctor) use ($user) {
                $like = UserDoctorLike::where('likeable_id', $user->id)
                    ->where('likeable_type', 'App\\Models\\User')
                    ->where('doctor_id', $doctor->id)
                    ->first();

                return [
                    'id'                 => $doctor->id,
                    'name'               => $doctor->first_name . ' ' . $doctor->last_name,
                    'specialty'          => $doctor->specialty ? $doctor->specialty->name : null,
                    'license_number'     => $doctor->license_number,
                    'profile_photo_path' => $doctor->profile_photo_path ? asset('storage/' . $doctor->profile_photo_path) : null,
                    'liked_at'           => $like ? $like->liked_at->toIso8601String() : null,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedDoctors,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * گرفتن لیست پزشکان برتر
     *
     * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
     * @response 200 {
     *   "status": "success",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "دکتر محمدی",
     *       "specialty": "قلب و عروق",
     *       "hospital": "بیمارستان کسری",
     *       "star_rating": 4.5,
     *       "appointment_count": 150,
     *       "image": "https://example.com/doctor1.jpg",
     *       "province": "تهران",
     *       "next_available_slot": "2025-03-17 10:00:00"
     *     }
     *   ]
     * }
     */
    public function getBestDoctors(Request $request)
    {
        try {
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            $bestDoctors = BestDoctor::where('status', true)
                ->with([
                    'doctor'       => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'specialty_id', 'profile_photo_path', 'province_id')
                            ->with([
                                'specialty'     => fn ($query) => $query->select('id', 'name'),
                                'province'      => fn ($query) => $query->select('id', 'name'),
                                'workSchedules' => fn ($query) => $query->where('is_working', true),
                                'appointmentConfig',
                            ]);
                    },
                    'hospital'     => fn ($query) => $query->select('id', 'name'),
                    'appointments' => fn ($query) => $query->where('appointments.status', 'scheduled'),
                ])
                ->select(['id', 'doctor_id', 'star_rating'])
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('star_rating', 'desc')
                ->get()
                ->groupBy('doctor_id')
                ->map(fn ($group) => $group->first());


            $formattedDoctors = $bestDoctors->map(function ($bestDoctor) {
                $slotData = $this->getNextAvailableSlot($bestDoctor->doctor);
                $jalaliDate = null;
                if ($slotData['next_available_slot']) {
                    $date       = Carbon::parse($slotData['next_available_slot']);
                    $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($date)->format('Y-m-d');
                }
                $result = [
                    'id'                  => optional($bestDoctor->doctor)->id,
                    'name'                => optional($bestDoctor->doctor)->first_name . ' ' . optional($bestDoctor->doctor)->last_name,
                    'specialty'           => optional(optional($bestDoctor->doctor)->specialty)->name,
                    'star_rating'         => $bestDoctor->star_rating,
                    'image'               => optional($bestDoctor->doctor)->profile_photo_path ? asset('storage/' . optional($bestDoctor->doctor)->profile_photo_path) : null,
                    'province'            => optional(optional($bestDoctor->doctor)->province)->name,
                    'next_available_slot' => $jalaliDate,
                ];
                return $result;
            })->values();


            return response()->json([
                'status' => 'success',
                'data'   => $formattedDoctors,
            ], 200);

        } catch (\Exception $e) {
            
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
       * گرفتن لیست پزشکان جدید
       *
       * @queryParam limit integer تعداد آیتم‌ها (اختیاری، اگر نباشد همه برگردانده می‌شود)
       * @response 200 {
       *   "status": "success",
       *   "data": [
       *     {
       *       "id": 1,
       *       "name": "میلاد کرمانجی",
       *       "specialty": "کارشناسی ارشد علوم تغذیه",
       *       "profile_photo_url": "http://127.0.0.1:8000/admin-assets/images/default-avatar.png",
       *       "created_at": "1403/12/28",
       *       "province": "دیواندره"
       *     }
       *   ]
       * }
       * @response 500 {
       *   "status": "error",
       *   "message": "خطای سرور",
       *   "data": null
       * }
       */
    public function getNewDoctors(Request $request)
    {
        try {
            // بررسی وجود پارامتر limit
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            // گرفتن پزشکان جدید بر اساس created_at با کش بهینه
            $newDoctors = Cache::remember('new_doctors_' . ($limit ?? 'all'), 300, function () use ($limit) {
                return \App\Models\Doctor::where('status', true)
                    ->with([
                        'specialty' => fn ($query) => $query->select('id', 'name'),
                        'province'  => fn ($query) => $query->select('id', 'name'),
                    ])
                    ->orderBy('created_at', 'desc')
                    ->when($limit, fn ($query) => $query->limit($limit))
                    ->get();
            });

            // فرمت کردن داده‌ها با تبدیل تاریخ شمسی
            $formattedDoctors = $newDoctors->map(function ($doctor) {
                $jalaliDate = $doctor->created_at
                    ? \Morilog\Jalali\Jalalian::fromCarbon(Carbon::parse($doctor->created_at))->format('Y/m/d')
                    : '---';

                return [
                    'id'               => $doctor->id,
                    'name'             => $doctor->fullName,
                    'specialty'        => $doctor->specialty ? $doctor->specialty->name : null,
                    'profile_photo_url' => $doctor->profile_photo_url,
                    'created_at'       => $jalaliDate,
                    'province'         => $doctor->province ? $doctor->province->name : null,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedDoctors,
            ], 200);

        } catch (\Exception $e) {
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
        $doctorId        = $doctor->id;
        $today           = Carbon::today('Asia/Tehran');
        $now             = Carbon::now('Asia/Tehran');
        $daysOfWeek      = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        $appointmentConfig = $doctor->appointmentConfig; // بدون کش
        $calendarDays      = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;
        $duration          = $appointmentConfig ? ($appointmentConfig->appointment_duration ?? 15) : 15;

        $schedules = $doctor->workSchedules; // بدون کش
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

                $startTime = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime   = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['end'], 'Asia/Tehran');

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $nextTime = (clone $currentTime)->addMinutes($duration);

                    $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
                        $apptStart = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time, 'Asia/Tehran');
                        $apptEnd   = (clone $apptStart)->addMinutes($duration);
                        return $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);
                    });

                    if (! $isBooked && $currentTime->gte($now)) {

                        return [
                            'next_available_slot' => $currentTime->toIso8601String(),
                            'max_appointments'    => $schedule->appointment_settings[0]['max_appointments'] ?? 22,
                        ];
                    }

                    $currentTime->addMinutes($duration);
                }
            }
        }

        return ['next_available_slot' => null, 'max_appointments' => 0];
    }
}
