<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Doctor;
use App\Models\Review;
use App\Models\DoctorTag;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorMessenger;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\CounselingAppointment;
use App\Models\DoctorCounselingConfig;
use App\Models\DoctorCounselingWorkSchedule;

class DoctorProfileController extends Controller
{
    /**
     * دریافت اطلاعات کامل پروفایل پزشک
     */
    public function getDoctorProfile(Request $request, $doctorId)
    {
        try {
            $doctor = Doctor::with([
             'specialty' => fn ($q) => $q->select('id', 'name'),
             'province'  => fn ($q) => $q->select('id', 'name'),
             'city'      => fn ($q) => $q->select('id', 'name'),
             'medicalCenters'   => fn ($q) => $q->where('is_active', true)->where('type', 'policlinic')
              ->select('medical_centers.id', 'medical_centers.name', 'medical_centers.address', 'medical_centers.province_id', 'medical_centers.city_id', 'medical_centers.phone_number', 'medical_centers.is_main_center'),
             'reviews'   => fn ($q) => $q->where('is_approved', true)
              ->with(['reviewable' => fn ($q) => $q->select('id', 'first_name', 'last_name')])
              ->orderBy('created_at', 'desc')
              ->limit(3),
             'messengers',
             'doctorTags',
             'counselingConfig',
             'appointmentConfig',
             'workSchedules',
             'insurances' => fn ($q) => $q->select('insurances.id', 'insurances.name'), // اصلاح شده
            ])->find($doctorId);

            if (!$doctor) {
                return response()->json([
                 'status'  => 'error',
                 'message' => 'پزشک یافت نشد',
                 'data'    => null,
                ], 404);
            }

            // اطلاعات اصلی پزشک
            $doctorData = [
             'id'              => $doctor->id,
             'name'            => $doctor->full_name,
             'specialty'       => $doctor->specialty ? $doctor->specialty->name : 'نامشخص',
             'avatar'          => $doctor->profile_photo_url,
             'location'        => $doctor->city ? $doctor->city->name : ($doctor->province ? $doctor->province->name : 'تهران'),
             'description'     => $doctor->description ?? 'دستیار تخصصی ارتوپدی دانشگاه علوم پزشکی شیراز، درمان کمردرد، درد مفاصل، آسیب تاندون‌ها، شکستگی، دررفتگی، استخوان و مفاصل',
             'medical_code'    => $doctor->license_number ?? '۱۵۴۶۲۳',
             'rating'          => $doctor->reviews->avg('rating') ?? 4.3,
             'reviews_count'   => $doctor->reviews->count() ?? 1903,
            ];

            // تگ‌ها
            $tags = $doctor->doctorTags->map(function ($tag) {
                return [
                 'name'       => $tag->name,
                 'color'      => $tag->color ?? '#D1FAE5', // رنگ پیش‌فرض
                 'text_color' => $tag->text_color ?? '#059669', // رنگ متن پیش‌فرض
                ];
            })->values()->all();

            if (empty($tags)) {
                $tags = [
                 ['name' => 'کمترین معطلی', 'color' => '#D1FAE5', 'text_color' => '#059669'],
                 ['name' => 'خوش برخورد', 'color' => '#FFEDD5', 'text_color' => '#EA580C'],
                 ['name' => 'پوشش بیمه', 'color' => '#FEF3C7', 'text_color' => '#D97706'],
                ];
            }

            // شبکه‌های اجتماعی
            $socialMedia = $this->getSocialMedia($doctor);

            // درباره پزشک
            $about = $doctor->bio ?? '';

            // نوبت‌های حضوری و آنلاین
            $appointments = $this->getAppointments($doctor);

            // آدرس و تلفن تماس
            $mainClinic = $doctor->medicalCenters->where('is_main_center', true)->first() ?? $doctor->medicalCenters->first();
            $addressData = [
             'address'      => $mainClinic ? $mainClinic->address : 'آدرس ی ثبت نشده',
             'phone_number' => $mainClinic ? $mainClinic->phone_number : 'نامشخص',
            ];

            // نظرات کاربران
            $reviews = $this->getReviews($doctor);

            // پاسخ نهایی
            return response()->json([
             'status' => 'success',
             'data'   => [
              'doctor'       => $doctorData,
              'tags'         => $tags,
              'social_media' => $socialMedia,
              'about'        => $about,
              'appointments' => $appointments,
              'address'      => $addressData,
              'reviews'      => $reviews,
             ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Doctor profile error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
             'status'  => 'error',
             'message' => 'خطای سرور',
             'data'    => null,
            ], 500);
        }
    }

    /**
     * دریافت شبکه‌های اجتماعی پزشک
     */
    private function getSocialMedia($doctor)
    {
        $messengers = $doctor->messengers;
        $socialMedia = [];

        foreach ($messengers as $messenger) {
            $socialMedia[] = [
             'type' => $messenger->messenger_type,
             'link' => $messenger->username ? "https://{$messenger->messenger_type}.com/{$messenger->username}" : $messenger->phone_number,
            ];
        }

        // اگر شبکه اجتماعی ثبت نشده، مقادیر پیش‌فرض
        if (empty($socialMedia)) {
            $socialMedia = [
             ['type' => 'website', 'link' => 'https://benobe.ir'],
             ['type' => 'whatsapp', 'link' => ''],
             ['type' => 'instagram', 'link' => ''],
            ];
        }

        return $socialMedia;
    }

    /**
     * دریافت اطلاعات نوبت‌ها
     */
    private function getAppointments($doctor)
    {
        $today = Carbon::today('Asia/Tehran');
        $mainClinic = $doctor->medicalCenters->where('is_main_center', true)->first() ?? $doctor->medicalCenters->first();

        // نوبت حضوری
        $inPersonSlotData = $this->getNextAvailableSlot($doctor, $mainClinic ? $mainClinic->id : null);
        $inPersonSlot = null;

        if (isset($inPersonSlotData['next_available_slot']) && $inPersonSlotData['next_available_slot']) {
            $slotString = $inPersonSlotData['next_available_slot']; // "3 فروردین 1404 ساعت 07:00"
            [$jalaliDate, $time] = explode(' ساعت ', $slotString);
            $jalaliDate = trim($jalaliDate); // "3 فروردین 1404"
            $time = trim($time); // "07:00"

            // تبدیل دستی تاریخ شمسی به میلادی
            $gregorianDate = $this->parseJalaliToGregorian($jalaliDate);
            if ($gregorianDate) {
                $inPersonSlot = (object) [
                 'date' => $gregorianDate->toDateString(), // مثلاً "2025-03-23"
                 'time' => Carbon::parse($time)->toTimeString(), // "07:00:00"
                ];
            }
        }

        $inPersonData = $inPersonSlot ? [
         'date_time' => $this->formatJalaliDateTime($inPersonSlot->date, $inPersonSlot->time),
         'address' => $mainClinic ? $mainClinic->address : '',
        ] : [
         'date_time' => '',
         'address' => $mainClinic ? $mainClinic->address : '',
        ];

        // نوبت آنلاین
        $onlineSlotData = $this->getNextAvailableOnlineSlot($doctor, 'phone');
        $onlineSlot = null;

        if (isset($onlineSlotData['next_available_slot']) && $onlineSlotData['next_available_slot']) {
            $slotString = $onlineSlotData['next_available_slot'];
            [$jalaliDate, $time] = explode(' ساعت ', $slotString);
            $jalaliDate = trim($jalaliDate);
            $time = trim($time);

            $gregorianDate = $this->parseJalaliToGregorian($jalaliDate);
            if ($gregorianDate) {
                $onlineSlot = (object) [
                 'date' => $gregorianDate->toDateString(),
                 'time' => Carbon::parse($time)->toTimeString(),
                 'fee' => 300000,
                 'consultation_types' => [
                  'phone' => $doctor->counselingConfig && $doctor->counselingConfig->has_phone_counseling,
                  'video' => $doctor->counselingConfig && $doctor->counselingConfig->has_video_counseling,
                  'text' => $doctor->counselingConfig && $doctor->counselingConfig->has_text_counseling,
                 ],
                ];
            }
        }

        $onlineData = $onlineSlot ? [
         'date_time' => $this->formatJalaliDateTime($onlineSlot->date, $onlineSlot->time),
         'fee' => $onlineSlot->fee ?? 300000,
         'consultation_types' => [
          'phone' => $onlineSlot->consultation_types['phone'] ?? ($doctor->counselingConfig && $doctor->counselingConfig->has_phone_counseling),
          'video' => $onlineSlot->consultation_types['video'] ?? ($doctor->counselingConfig && $doctor->counselingConfig->has_video_counseling),
          'text' => $onlineSlot->consultation_types['text'] ?? ($doctor->counselingConfig && $doctor->counselingConfig->has_text_counseling),
         ],
        ] : [
         'date_time' => '',
         'fee' => 300000,
         'consultation_types' => [
          'phone' => true,
          'video' => true,
          'text' => true,
         ],
        ];

        return [
         'in_person' => $inPersonData,
         'online' => $onlineData,
        ];
    }

    private function parseJalaliToGregorian($jalaliDate)
    {
        $persianMonths = [
         'فروردین' => 1,
         'اردیبهشت' => 2,
         'خرداد' => 3,
         'تیر' => 4,
         'مرداد' => 5,
         'شهریور' => 6,
         'مهر' => 7,
         'آبان' => 8,
         'آذر' => 9,
         'دی' => 10,
         'بهمن' => 11,
         'اسفند' => 12,
        ];

        $parts = explode(' ', trim($jalaliDate)); // "3 فروردین 1404" -> ["3", "فروردین", "1404"]
        if (count($parts) !== 3) {
            return null;
        }

        $day = (int) $parts[0];
        $monthName = $parts[1];
        $year = (int) $parts[2];

        $month = $persianMonths[$monthName] ?? null;
        if (!$month) {
            return null;
        }

        // تبدیل دقیق‌تر با استفاده از Jalalian برای محاسبه تاریخ میلادی
        try {
            $jalali = new \Morilog\Jalali\Jalalian($year, $month, $day);
            return $jalali->toCarbon();
        } catch (\Exception $e) {

            // در صورت خطا، تقریب اولیه
            $gregorianYear = $year - 621;
            $gregorianDate = Carbon::create($gregorianYear, $month, $day, 0, 0, 0, 'Asia/Tehran');
            return $gregorianDate;
        }
    }

    private function formatJalaliDateTime($gregorianDate, $time)
    {
        $carbonDate = Carbon::parse("$gregorianDate $time", 'Asia/Tehran');
        $jalali = Jalalian::fromCarbon($carbonDate);
        return $jalali->format('l j F Y ساعت H:i');
    }

    /**
     * دریافت نظرات کاربران
     */
    private function getReviews($doctor)
    {
        $reviews = $doctor->reviews->map(function ($review) {
            $user = $review->reviewable;
            return [
             'user_name' => $user ? $user->full_name : '',
             'date'      => $review->created_at ? Jalalian::fromCarbon($review->created_at)->ago() : '',
             'type'      => '', // می‌تونید منطق پیچیده‌تر برای نوع نوبت اضافه کنید
             'rating'    => $review->rating ?? 4,
             'comment'   => $review->comment ?? '',
            ];
        })->values()->all();

        if (empty($reviews)) {
            $reviews = [
             [
              'user_name' => '',
              'date'      => '',
              'type'      => '',
              'rating'    => '',
              'comment'   => '',
             ],
            ];
        }

        return $reviews;
    }
    private function getNextAvailableSlot($doctor, $clinicId)
    {
        $doctorId = $doctor->id;
        $today = Carbon::today('Asia/Tehran');
        $now = Carbon::now('Asia/Tehran');
        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        $appointmentConfig = $doctor->appointmentConfig;
        $calendarDays = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;
        $duration = $appointmentConfig ? ($appointmentConfig->appointment_duration ?? 15) : 15;

        $schedules = $doctor->workSchedules;
        if ($schedules->isEmpty()) {
            return ['next_available_slot' => null, 'slots' => [], 'max_appointments' => 0];
        }

        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->when($clinicId, function ($query) use ($clinicId) {
                return $query->where('medical_center_id', $clinicId);
            })
            ->where(function ($query) {
                $query->where('status', 'scheduled')
                      ->orWhere('status', 'pending_review');
            })
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get();

        $slots = [];
        $nextAvailableSlot = null;

        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $dayName = $daysOfWeek[$checkDayIndex];
            $checkDate = $today->copy()->addDays($i);
            $jalaliDate = Jalalian::fromCarbon($checkDate)->format('j F Y');
            $persianDayName = Jalalian::fromCarbon($checkDate)->format('l');

            $dayAppointments = $bookedAppointments->filter(function ($appointment) use ($checkDate) {
                return Carbon::parse($appointment->appointment_date)->isSameDay($checkDate);
            });

            $activeSlots = [];
            $inactiveSlots = [];

            foreach ($schedules as $schedule) {
                if ($schedule->day !== $dayName) {
                    continue;
                }

                $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                if (!is_array($workHours) || empty($workHours)) {
                    continue;
                }
                $workHour = $workHours[0];

                $startTime = Carbon::parse("{$checkDate->toDateString()} {$workHour['start']}", 'Asia/Tehran');
                $endTime = Carbon::parse("{$checkDate->toDateString()} {$workHour['end']}", 'Asia/Tehran');

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $nextTime = $currentTime->copy()->addMinutes($duration);
                    $slotTime = $currentTime->format('H:i');

                    $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
                        $dateOnly = Carbon::parse($appointment->appointment_date)->toDateString();
                        $timeOnly = Carbon::parse($appointment->appointment_time)->format('H:i:s');
                        $combinedDateTime = $dateOnly . ' ' . $timeOnly;
                        $apptStart = Carbon::parse($combinedDateTime, 'Asia/Tehran');
                        $apptEnd = $apptStart->copy()->addMinutes($duration);
                        $isOverlapping = $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);

                        return $isOverlapping;
                    });

                    if ($checkDate->isToday()) {
                        if ($isBooked || $currentTime->lt($now)) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                            }
                        }
                    } else {
                        if ($isBooked) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                            }
                        }
                    }

                    $currentTime->addMinutes($duration);
                }
            }

            if (!empty($activeSlots) || !empty($inactiveSlots)) {
                $slots[] = [
                    'date' => $jalaliDate,
                    'day_name' => $persianDayName,
                    'available_slots' => $activeSlots,
                    'available_count' => count($activeSlots),
                    'inactive_slots' => $inactiveSlots,
                    'inactive_count' => count($inactiveSlots),
                ];
            }
        }

        // استفاده از helper function برای استخراج max_appointments
        $appointmentSettings = $schedules->first()->appointment_settings ?? [];
        if (is_string($appointmentSettings)) {
            $appointmentSettings = json_decode($appointmentSettings, true) ?? [];
        }
        $maxAppointments = $this->getMaxAppointmentsFromSettings($appointmentSettings, 22);

        $result = [
            'next_available_slot' => $nextAvailableSlot,
            'slots' => $slots,
            'max_appointments' => $maxAppointments,
        ];

        return $result;
    }

    private function getNextAvailableOnlineSlot($doctor, $type)
    {
        $doctorId = $doctor->id;
        $today = Carbon::today('Asia/Tehran');
        $now = Carbon::now('Asia/Tehran');
        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        $counselingConfig = DoctorCounselingConfig::where('doctor_id', $doctorId)->first();
        $calendarDays = $counselingConfig ? ($counselingConfig->calendar_days ?? 30) : 30;
        $duration = $counselingConfig ? ($counselingConfig->appointment_duration ?? 15) : 15;

        $schedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
            ->where('is_working', true)
            ->get();
        if ($schedules->isEmpty()) {
            return ['next_available_slot' => null, 'slots' => []];
        }

        $bookedAppointments = CounselingAppointment::where('doctor_id', $doctorId)
            ->where('appointment_type', $type)
            ->where(function ($query) {
                $query->where('status', 'scheduled')
                      ->orWhere('status', 'pending_review');
            })
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get();

        $slots = [];
        $nextAvailableSlot = null;

        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $dayName = $daysOfWeek[$checkDayIndex];
            $checkDate = $today->copy()->addDays($i);
            $jalaliDate = Jalalian::fromCarbon($checkDate)->format('j F Y');
            $persianDayName = Jalalian::fromCarbon($checkDate)->format('l');

            // فیلتر کردن نوبت‌ها برای این روز خاص
            $dayAppointments = $bookedAppointments->filter(function ($appointment) use ($checkDate) {
                return Carbon::parse($appointment->appointment_date)->isSameDay($checkDate);
            });

            $activeSlots = [];
            $inactiveSlots = [];

            foreach ($schedules as $schedule) {
                if ($schedule->day !== $dayName) {
                    continue;
                }

                $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                if (!is_array($workHours) || empty($workHours)) {
                    continue;
                }
                $workHour = $workHours[0];

                $startTime = Carbon::parse("{$checkDate->toDateString()} {$workHour['start']}", 'Asia/Tehran');
                $endTime = Carbon::parse("{$checkDate->toDateString()} {$workHour['end']}", 'Asia/Tehran');

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $nextTime = $currentTime->copy()->addMinutes($duration);
                    $slotTime = $currentTime->format('H:i');

                    $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
                        $apptStart = Carbon::parse("{$appointment->appointment_date} {$appointment->appointment_time}", 'Asia/Tehran');
                        $apptEnd = $apptStart->copy()->addMinutes($duration);
                        $isOverlapping = $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);

                        return $isOverlapping;
                    });

                    if ($checkDate->isToday()) {
                        if ($isBooked || $currentTime->lt($now)) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                            }
                        }
                    } else {
                        if ($isBooked) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                            }
                        }
                    }

                    $currentTime->addMinutes($duration);
                }
            }

            if (!empty($activeSlots) || !empty($inactiveSlots)) {
                $slots[] = [
                    'date' => $jalaliDate,
                    'day_name' => $persianDayName,
                    'available_slots' => $activeSlots,
                    'available_count' => count($activeSlots),
                    'inactive_slots' => $inactiveSlots,
                    'inactive_count' => count($inactiveSlots),
                ];
            }
        }

        $result = [
            'next_available_slot' => $nextAvailableSlot,
            'slots' => $slots,
        ];

        return $result;
    }

    /**
     * استخراج مدت زمان نوبت از appointment_settings برای روز مشخص
     * پشتیبانی از فرمت‌های قدیمی و جدید
     */
    private function getAppointmentDuration($appointmentSettings, $dayOfWeek, $defaultDuration = 15)
    {
        // اطمینان از اینکه appointmentSettings آرایه است
        if (empty($appointmentSettings) || !is_array($appointmentSettings)) {
            return $defaultDuration;
        }

        // فرمت جدید: هر آیتم شامل 'day' field
        if (isset($appointmentSettings[0]['day'])) {
            // پیدا کردن تنظیمات برای روز فعلی
            foreach ($appointmentSettings as $setting) {
                if (is_array($setting) && isset($setting['day']) && $setting['day'] === $dayOfWeek) {
                    return $setting['appointment_duration'] ?? $defaultDuration;
                }
            }
            return $defaultDuration;
        }

        // فرمت قدیمی: هر آیتم شامل 'days' array
        foreach ($appointmentSettings as $setting) {
            if (is_array($setting) && isset($setting['days']) && is_array($setting['days']) && in_array($dayOfWeek, $setting['days'])) {
                return $setting['appointment_duration'] ?? $defaultDuration;
            }
        }

        return $defaultDuration;
    }

    /**
     * استخراج حداکثر تعداد نوبت از appointment_settings
     * پشتیبانی از فرمت‌های قدیمی و جدید
     */
    private function getMaxAppointmentsFromSettings($appointmentSettings, $defaultMaxAppointments = 22)
    {
        // اطمینان از اینکه appointmentSettings آرایه است
        if (empty($appointmentSettings) || !is_array($appointmentSettings)) {
            return $defaultMaxAppointments;
        }

        // فرمت جدید: هر آیتم شامل 'day' field
        if (isset($appointmentSettings[0]['day'])) {
            // پیدا کردن اولین تنظیمات که max_appointments داشته باشد
            foreach ($appointmentSettings as $setting) {
                if (is_array($setting) && isset($setting['max_appointments']) && $setting['max_appointments'] > 0) {
                    return $setting['max_appointments'];
                }
            }
            return $defaultMaxAppointments;
        }

        // فرمت قدیمی: هر آیتم شامل 'days' array
        foreach ($appointmentSettings as $setting) {
            if (is_array($setting) && isset($setting['max_appointments']) && $setting['max_appointments'] > 0) {
                return $setting['max_appointments'];
            }
        }

        return $defaultMaxAppointments;
    }
}
