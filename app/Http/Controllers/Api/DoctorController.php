<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\BestDoctor;
use App\Models\UserDoctorLike;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

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
                $user = JWTAuth::setToken($token)->authenticate();
                if (! $user) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کاربر یافت نشد',
                        'data'    => null,
                    ], 401);
                }
            } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
                Log::error('GetMyDoctors - JWT Error: ' . $e->getMessage());
                return response()->json([
                    'status'  => 'error',
                    'message' => 'توکن نامعتبر است: ' . $e->getMessage(),
                    'data'    => null,
                ], 401);
            }

            // گرفتن پزشکان لایک‌شده
            $likedDoctors = UserDoctorLike::where('user_id', $user->id)
                ->with([
                    'doctor' => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'specialty_id', 'license_number', 'profile_photo_path')
                            ->with(['specialty' => function ($query) {
                                $query->select('id', 'name');
                            }]);
                    },
                ])
                ->select('id', 'user_id', 'doctor_id', 'liked_at')
                ->get()
                ->pluck('doctor')
                ->filter();

            // فرمت کردن داده‌ها
            $formattedDoctors = $likedDoctors->map(function ($doctor) {
                $like = UserDoctorLike::where('user_id', auth()->id())
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
            Log::error('GetMyDoctors - Error: ' . $e->getMessage());
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
            // بررسی وجود پارامتر limit
            $limit = $request->has('limit') ? (int) $request->input('limit') : null;

            // گرفتن پزشکان برتر با Eager Loading
            $bestDoctors = BestDoctor::where('status', true)
                ->with([
                    'doctor'       => function ($query) {
                        $query->select('id', 'first_name', 'last_name', 'specialty_id', 'profile_photo_path', 'province_id')
                            ->with([
                                'specialty'     => fn($query)     => $query->select('id', 'name'),
                                'province'      => fn($query)      => $query->select('id', 'name'),
                                'workSchedules' => fn($query) => $query->where('is_working', true),
                                'appointmentConfig',
                            ]);
                    },
                    'hospital'     => fn($query)     => $query->select('id', 'name'),
                    'appointments' => fn($query) => $query->where('appointments.status', 'scheduled'),
                ])
                ->select('id', 'doctor_id', 'hospital_id', 'star_rating')
                ->when($limit !== null, function ($query) use ($limit) {
                    return $query->limit($limit);
                })
                ->orderBy('star_rating', 'desc')
                ->get()
                ->groupBy('doctor_id')
                ->map(fn($group) => $group->first());

            // فرمت کردن داده‌ها
            $formattedDoctors = $bestDoctors->map(function ($bestDoctor) {
                // تعداد نوبت‌های رزرو شده
                $appointmentCount = $bestDoctor->appointments->count();

                // محاسبه اولین نوبت خالی و حداکثر نوبت‌ها
                $slotData = $this->getNextAvailableSlot($bestDoctor->doctor);

                // تبدیل تاریخ به شمسی اگه وجود داشته باشه
                $jalaliDate = null;
                if ($slotData['next_available_slot']) {
                    $date       = Carbon::parse($slotData['next_available_slot']);
                    $jalaliDate = \Morilog\Jalali\Jalalian::fromCarbon($date)->format('Y-m-d H:i');
                }

                return [
                    'id'                  => $bestDoctor->doctor->id,
                    'name'                => $bestDoctor->doctor->first_name . ' ' . $bestDoctor->doctor->last_name,
                    'specialty'           => $bestDoctor->doctor->specialty ? $bestDoctor->doctor->specialty->name : null,
                    'hospital'            => $bestDoctor->hospital ? $bestDoctor->hospital->name : null,
                    'star_rating'         => $bestDoctor->star_rating,
                    'appointment_count'   => $appointmentCount,
                    'max_appointments'    => $slotData['max_appointments'],
                    'image'               => $bestDoctor->doctor->profile_photo_path ? asset('storage/' . $bestDoctor->doctor->profile_photo_path) : null,
                    'province'            => $bestDoctor->doctor->province ? $bestDoctor->doctor->province->name : null,
                    'next_available_slot' => $jalaliDate,
                ];
            })->values();

            return response()->json([
                'status' => 'success',
                'data'   => $formattedDoctors,
            ], 200);

        } catch (\Exception $e) {
            Log::error('GetBestDoctors - Error: ' . $e->getMessage());
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
                        'specialty' => fn($query) => $query->select('id', 'name'),
                        'province'  => fn($query) => $query->select('id', 'name'),
                    ])
                    ->orderBy('created_at', 'desc')
                    ->when($limit, fn($query) => $query->limit($limit))
                    ->get(['id', 'first_name', 'last_name', 'specialty_id', 'profile_photo_path', 'province_id', 'created_at']);
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
            Log::error('GetNewDoctors - Error: ' . $e->getMessage());
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
        $today           = Carbon::today();
        $daysOfWeek      = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        // کش کردن تنظیمات تقوim
        $appointmentConfig = Cache::remember("appointment_config_{$doctorId}", 3600, function () use ($doctor) {
            return $doctor->appointmentConfig;
        });

        // تعداد روزهایی که باید بررسی کنیم
        $calendarDays = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;

        // کش کردن برنامه‌های کاری
        $schedules = Cache::remember("work_schedules_{$doctorId}", 3600, function () use ($doctor) {
            return $doctor->workSchedules;
        });

        if ($schedules->isEmpty()) {
            return ['next_available_slot' => null, 'max_appointments' => 0];
        }

        // گرفتن نوبت‌های رزرو شده (اینجا کش نمی‌کنیم چون داده‌ها پویا هستن)
        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('appointments.status', 'scheduled')
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get()
            ->groupBy('appointment_date');

        // بررسی روزهای آینده
        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $dayName       = $daysOfWeek[$checkDayIndex];
            $checkDate     = $today->copy()->addDays($i);

            // نوبت‌های رزرو شده برای این روز
            $dayAppointments = $bookedAppointments->get($checkDate->toDateString(), collect());

            foreach ($schedules as $schedule) {
                $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                if (! is_array($workHours)) {
                    Log::warning("Invalid work_hours JSON for doctor_id: {$doctorId}, schedule_id: {$schedule->id}");
                    continue;
                }

                foreach ($workHours as $workHour) {
                    $startTime = (clone $checkDate)->setTimeFromTimeString($workHour['start']);
                    $endTime   = (clone $checkDate)->setTimeFromTimeString($workHour['end']);

                    $appointmentSettings = is_string($schedule->appointment_settings) ? json_decode($schedule->appointment_settings, true) : $schedule->appointment_settings;
                    if (! is_array($appointmentSettings)) {
                        Log::warning("Invalid appointment_settings JSON for doctor_id: {$doctorId}, schedule_id: {$schedule->id}");
                        continue;
                    }

                    foreach ($appointmentSettings as $appointmentSetting) {
                        $selectedDay = $appointmentSetting['selected_day'];
                        if ($daysOfWeek[$checkDayIndex] !== $selectedDay) {
                            continue;
                        }

                        $maxAppointments = $appointmentSetting['max_appointments'] ?? $workHour['max_appointments'] ?? 10;
                        $duration        = $appointmentConfig->appointment_duration ?? 30;

                        $currentTime        = $startTime;
                        $appointmentsBooked = $dayAppointments->count();

                        while ($currentTime < $endTime && $appointmentsBooked < $maxAppointments) {
                            $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime) {
                                return Carbon::parse($appointment->start_time)->eq($currentTime);
                            });

                            if (! $isBooked) {
                                return [
                                    'next_available_slot' => $currentTime->toIso8601String(),
                                    'max_appointments'    => $maxAppointments,
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
