<?php

namespace App\Http\Controllers\Api;

use App\Models\Doctor;
use App\Models\Clinic;
use App\Models\MedicalCenter;
use App\Models\CounselingAppointment;
use App\Models\DoctorCounselingConfig;
use App\Models\DoctorCounselingWorkSchedule;
use App\Models\DoctorAppointmentConfig;
use App\Models\DoctorWorkSchedule;
use App\Models\Appointment;
use App\Models\Insurance;
use App\Models\Service;
use App\Models\Zone;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorNote;
use App\Models\DoctorHoliday;
use App\Models\DoctorHolidays;
use App\Models\SpecialDailySchedule;
use App\Models\SpecialDailySchedules;
use App\Models\CounselingDailySchedule;
use App\Models\DoctorCounselingHoliday;
use App\Models\CounselingDailySchedules;
use App\Models\DoctorCounselingHolidays;
use Illuminate\Support\Facades\Log;

class DoctorAppointmentController extends Controller
{
    public function getAppointmentOptions(Request $request, $doctorId)
    {
        try {
            $user = Auth::user();

            $doctor = Doctor::find($doctorId);
            if (!$doctor) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'پزشک یافت نشد',
                    'data'    => null,
                ], 404);
            }

            $selectedClinicId = $request->query('clinic_id');
            if ($selectedClinicId !== null) {
                if (!is_numeric($selectedClinicId) || $selectedClinicId <= 0) {
                    $selectedClinicId = null;
                }
            }

            $clinics = MedicalCenter::whereHas('doctors', function ($query) use ($doctorId) {
                $query->where('doctor_id', $doctorId);
            })
            ->where('type', 'clinic')
            ->where('is_active', true)
            ->select('id', 'name', 'province_id', 'city_id', 'address', 'phone_number', 'is_main_clinic')
            ->get();

            $selectedClinic = null;
            if ($selectedClinicId) {
                $selectedClinic = $clinics->where('id', (int)$selectedClinicId)->first();
                // اگر کلینیک پیدا نشد، selected_clinic را null بگذار و خطا نده
            }

            // اگر کلینیک انتخاب شده باشد، فقط همان کلینیک و نوبت حضوری آن را بده
            if ($selectedClinic) {
                $inPersonData = $this->getInPersonAppointmentData($doctor, $selectedClinic);
            } else {
                // اگر کلینیک انتخاب نشده یا پیدا نشد، اطلاعات همه کلینیک‌ها و نوبت حضوری همه را بده
                $inPersonData = $this->getInPersonAppointmentDataForAllClinics($doctor, $clinics);
            }
            $onlineData = $this->getOnlineAppointmentData($doctor);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'doctor' => [
                        'name'        => $doctor->full_name,
                        'slug'        => $doctor->slug,
                        'specialty'   => $doctor->specialty()->value('name') ?? 'نامشخص',
                        'province'    => $doctor->province()->value('name'),
                        'city'        => $doctor->city()->value('name'),
                        'views_count' => $doctor->views_count ?? 0,
                        'rating'      => $doctor->rating ?? 0,
                    ],
                    'clinics' => $clinics->map(function ($clinic) {
                        return [
                            'id'             => $clinic->id,
                            'name'           => $clinic->name,
                            'province'       => $clinic->province ? $clinic->province->name : null,
                            'city'           => $clinic->city ? $clinic->city->name : null,
                            'address'        => $clinic->address,
                            'phone_number'   => $clinic->phone_number,
                            'is_main_clinic' => $clinic->is_main_clinic,
                        ];
                    }),
                    'selected_clinic' => $selectedClinic ? [
                        'id'           => $selectedClinic->id,
                        'name'         => $selectedClinic->name,
                        'province'     => $selectedClinic->province ? $selectedClinic->province->name : null,
                        'city'         => $selectedClinic->city ? $selectedClinic->city->name : null,
                        'address'      => $selectedClinic->address,
                        'phone_number' => $selectedClinic->phone_number,
                    ] : null,
                    'appointment_types' => [
                        'in_person' => $inPersonData,
                        'online'    => array_merge(
                            ['note' => 'This section is populated using doctor_counseling_configs for duration/calendar and doctor_counseling_work_schedules for work hours, with booked slots from counseling_appointments'],
                            $onlineData
                        ),
                    ],
                ],
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور. لطفاً دوباره تلاش کنید.',
                'data'    => null,
            ], 500);
        }
    }

    // Helper to get DoctorAppointmentConfig for a doctor and clinic, strict by clinic_id
    private function getDoctorAppointmentConfig($doctorId, $clinicId = null)
    {
        if (is_null($clinicId)) {
            // فقط رکورد جنرال
            return \App\Models\DoctorAppointmentConfig::where('doctor_id', $doctorId)
                ->whereNull('clinic_id')
                ->first();
        } else {
            // فقط رکورد اختصاصی کلینیک
            return \App\Models\DoctorAppointmentConfig::where('doctor_id', $doctorId)
                ->where('clinic_id', $clinicId)
                ->first();
        }
    }

    private function getInPersonAppointmentDataForAllClinics($doctor, $clinics)
    {
        $data = [
            'next_available_slot' => null,
            'next_available_datetime' => null,
            'clinics' => [],
        ];

        foreach ($clinics as $clinic) {
            $slotData = $this->getNextAvailableSlot($doctor, $clinic->id);
            $notes = DoctorNote::where('doctor_id', $doctor->id)
                ->where('clinic_id', $clinic->id)
                ->where('appointment_type', 'in_person')
                ->first();
            // استفاده از helper جدید
            $appointmentConfig = $this->getDoctorAppointmentConfig($doctor->id, $clinic->id);
            $autoScheduling = $appointmentConfig ? $appointmentConfig->auto_scheduling : 1;

            if ($slotData['next_available_slot']) {
                $data['clinics'][] = [
                    'clinic_id' => $clinic->id,
                    'name'      => $clinic->name,
                    'province'  => $clinic->province ? $clinic->province->name : null,
                    'city'      => $clinic->city ? $clinic->city->name : null,
                    'address'   => $clinic->address,
                    'notes'     => $notes ? $notes->notes : 'ملاحظات خاصی برای این نوبت ثبت نشده است',
                    'next_available_slot' => $slotData['next_available_slot'],
                    'next_available_datetime' => $slotData['next_available_datetime'],
                    'slots' => $slotData['slots'],
                    'auto_scheduling' => $autoScheduling,
                ];
                if (!$data['next_available_datetime'] || Carbon::parse($slotData['next_available_datetime'])->lt(Carbon::parse($data['next_available_datetime']))) {
                    $data['next_available_slot'] = $slotData['next_available_slot'];
                    $data['next_available_datetime'] = $slotData['next_available_datetime'];
                }
            }
        }

        return $data;
    }

    private function getInPersonAppointmentData($doctor, $clinic)
    {
        $slotData = $this->getNextAvailableSlot($doctor, $clinic->id);
        $notes = DoctorNote::where('doctor_id', $doctor->id)
            ->where('clinic_id', $clinic->id)
            ->where('appointment_type', 'in_person')
            ->first();
        // استفاده از helper جدید
        $appointmentConfig = $this->getDoctorAppointmentConfig($doctor->id, $clinic->id);
        $autoScheduling = $appointmentConfig ? $appointmentConfig->auto_scheduling : 1;

        return [
            'next_available_slot' => $slotData['next_available_slot'],
            'next_available_datetime' => $slotData['next_available_datetime'],
            'clinic' => [
                'clinic_id' => $clinic->id,
                'name'      => $clinic->name,
                'province'  => $clinic->province ? $clinic->province->name : null,
                'city'      => $clinic->city ? $clinic->city->name : null,
                'address'   => $clinic->address,
                'notes'     => $notes ? $notes->notes : 'ملاحظات خاصی برای این نوبت ثبت نشده است',
                'slots'     => $slotData['slots'],
                'auto_scheduling' => $autoScheduling,
            ],
        ];
    }

    private function getOnlineAppointmentData($doctor)
    {
        $counselingConfig = DoctorCounselingConfig::where('doctor_id', $doctor->id)->first();
        $fee = $counselingConfig ? ($counselingConfig->price_15min ?? 100000) : 100000;

        $onlineTypes = [
            ['type' => 'phone', 'name' => 'مشاوره تلفنی', 'fee' => $fee, 'appointment_type' => 'phone'],
            ['type' => 'text', 'name' => 'مشاوره متنی', 'fee' => $fee, 'appointment_type' => 'text'],
        ];

        $data = ['types' => []];
        foreach ($onlineTypes as $type) {
            $slotData = $this->getNextAvailableOnlineSlot($doctor, $type['type']);
            $notes = DoctorNote::where('doctor_id', $doctor->id)
                ->where('appointment_type', $type['appointment_type'])
                ->first();
            $data['types'][] = [
                'type' => $type['type'],
                'name' => $type['name'],
                'fee' => $type['fee'],
                'notes' => $notes ? $notes->notes : 'ملاحظات خاصی برای این نوع مشاوره ثبت نشده است',
                'next_available_slot' => $slotData['next_available_slot'],
                'next_available_datetime' => $slotData['next_available_datetime'],
                'slots' => $slotData['slots'],
            ];
        }

        return $data;
    }

    private function getNextAvailableSlot($doctor, $clinicId)
    {
        $doctorId = $doctor->id;
        $today = Carbon::today('Asia/Tehran');
        $now = Carbon::now('Asia/Tehran');
        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        // دریافت تنظیمات نوبت‌دهی
        $appointmentConfig = $this->getDoctorAppointmentConfig($doctor->id, $clinicId);
        $calendarDays = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;
        $defaultDuration = $appointmentConfig ? ($appointmentConfig->appointment_duration ?? 15) : 15;
        $autoScheduling = $appointmentConfig ? $appointmentConfig->auto_scheduling : 1;
        if (!$autoScheduling) {
            return [
                'next_available_slot' => null,
                'next_available_datetime' => null,
                'slots' => [],
                'max_appointments' => null,
            ];
        }

        // دریافت نوبت‌های رزروشده
        $bookedAppointments = Appointment::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where(function ($query) {
                $query->where('status', 'scheduled')
                    ->orWhere('status', 'pending_review');
            })
            ->where('appointment_date', '>=', $today->toDateString())
            ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
            ->get();

        $slots = [];
        $nextAvailableSlot = null;
        $nextAvailableDateTime = null;

        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $dayName = $daysOfWeek[$checkDayIndex];
            $checkDate = $today->copy()->addDays($i);
            $checkDateString = $checkDate->toDateString();
            $jalaliDate = Jalalian::fromCarbon($checkDate)->format('j F Y');
            $persianDayName = Jalalian::fromCarbon($checkDate)->format('l');

            // بررسی تعطیلات
            $holidays = DoctorHoliday::where('doctor_id', $doctorId)
                ->where('clinic_id', $clinicId)
                ->where('status', 'active')
                ->first();
            if ($holidays && $holidays->holiday_dates) {
                $holidayDates = is_string($holidays->holiday_dates) ? json_decode($holidays->holiday_dates, true) : $holidays->holiday_dates;
                if (in_array($checkDateString, $holidayDates)) {

                    continue;
                }
            }

            // بررسی برنامه روزانه خاص
            $specialSchedule = SpecialDailySchedule::where('doctor_id', $doctorId)
                ->where('clinic_id', $clinicId)
                ->where('date', $checkDateString)
                ->first();

            $schedule = null;
            $workHours = [];
            $emergencyTimes = [];
            $duration = $defaultDuration; // مقدار پیش‌فرض
            $maxAppointments = null;

            if ($specialSchedule) {
                $workHours = is_string($specialSchedule->work_hours) ? json_decode($specialSchedule->work_hours, true) : $specialSchedule->work_hours;
                $emergencyTimes = is_string($specialSchedule->emergency_times) ? json_decode($specialSchedule->emergency_times, true) : ($specialSchedule->emergency_times ?? []);
                if (!empty($workHours) && isset($workHours[0]['max_appointments'])) {
                    $maxAppointments = $workHours[0]['max_appointments'];
                    // محاسبه duration بر اساس max_appointments
                    $startTime = Carbon::parse($checkDateString . ' ' . $workHours[0]['start'], 'Asia/Tehran');
                    $endTime = Carbon::parse($checkDateString . ' ' . $workHours[0]['end'], 'Asia/Tehran');
                    $totalMinutes = $startTime->diffInMinutes($endTime);
                    $duration = floor($totalMinutes / $maxAppointments);

                }
            } else {
                $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                    ->where('clinic_id', $clinicId)
                    ->where('day', $dayName)
                    ->where('is_working', true)
                    ->first();
                if (!$schedule) {
                    $schedule = DoctorWorkSchedule::where('doctor_id', $doctorId)
                        ->whereNull('clinic_id')
                        ->where('day', $dayName)
                        ->where('is_working', true)
                        ->first();
                }
                if ($schedule) {
                    $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                    $emergencyTimes = is_string($schedule->emergency_times) ? json_decode($schedule->emergency_times, true) : ($schedule->emergency_times ?? []);
                    if (!empty($workHours) && isset($workHours[0]['max_appointments'])) {
                        $maxAppointments = $workHours[0]['max_appointments'];
                    }
                }
            }

            if (empty($workHours)) {
                continue;
            }

            // نرمال‌سازی emergencyTimes
            $normalizedEmergencyTimes = [];
            if (is_array($emergencyTimes)) {
                foreach ($emergencyTimes as $index => $times) {
                    if (is_array($times)) {
                        if (!empty($times) && is_array($times[0])) {
                            $normalizedEmergencyTimes = array_merge($normalizedEmergencyTimes, $times[0]);
                        } else {
                            $normalizedEmergencyTimes = array_merge($normalizedEmergencyTimes, $times);
                        }
                    } elseif (is_string($times)) {
                        $normalizedEmergencyTimes[] = $times;
                    }
                }
            }



            $dayAppointments = $bookedAppointments->filter(function ($appointment) use ($checkDate) {
                return Carbon::parse($appointment->appointment_date)->isSameDay($checkDate);
            });

            // جمع‌آوری زمان‌های رزروشده
            $bookedTimes = [];
            foreach ($dayAppointments as $appointment) {
                try {
                    $time = $appointment->appointment_time;
                    if (preg_match('/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/', $time)) {
                        $correctedTime = Carbon::parse($time, 'Asia/Tehran')->format('H:i:s');

                        $time = $correctedTime;
                    } elseif (!preg_match('/\d{2}:\d{2}:\d{2}/', $time)) {

                        continue;
                    }
                    $bookedTimes[] = $time;
                } catch (\Exception $e) {

                }
            }

            $activeSlots = [];
            $inactiveSlots = [];

            foreach ($workHours as $workHour) {
                if (!isset($workHour['start']) || !isset($workHour['end'])) {

                    continue;
                }

                $startTime = Carbon::parse($checkDateString . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime = Carbon::parse($checkDateString . ' ' . $workHour['end'], 'Asia/Tehran');

                // محاسبه appointment_duration بر اساس max_appointments
                if (isset($workHour['max_appointments']) && $workHour['max_appointments'] > 0) {
                    $totalMinutes = $startTime->diffInMinutes($endTime);
                    $duration = floor($totalMinutes / $workHour['max_appointments']);

                } else {

                    continue;
                }

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $slotTime = $currentTime->format('H:i');
                    $slotTimeWithSeconds = $currentTime->format('H:i:s');

                    // بررسی زمان‌های اورژانسی
                    $isEmergency = false;
                    foreach ($normalizedEmergencyTimes as $emergencyTime) {
                        try {
                            if (is_string($emergencyTime)) {
                                $emergencyTimeParsed = Carbon::parse($checkDateString . ' ' . $emergencyTime, 'Asia/Tehran');
                                if ($currentTime->format('H:i') === $emergencyTimeParsed->format('H:i')) {
                                    $isEmergency = true;
                                    break;
                                }
                            } else {

                            }
                        } catch (\Exception $e) {

                        }
                    }

                    if ($isEmergency) {
                        $inactiveSlots[] = $slotTime;
                        $currentTime->addMinutes($duration);
                        continue;
                    }

                    // بررسی رزروهای قبلی
                    $isBooked = false;
                    foreach ($bookedTimes as $bookedTime) {
                        if (substr($bookedTime, 0, 5) === $slotTime) {
                            $isBooked = true;
                            break;
                        }
                    }

                    if ($checkDate->isToday()) {
                        if ($isBooked || $currentTime->lt($now)) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                                $nextAvailableDateTime = $currentTime->toDateTimeString();
                            }
                        }
                    } else {
                        if ($isBooked) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                                $nextAvailableDateTime = $currentTime->toDateTimeString();
                            }
                        }
                    }

                    $currentTime->addMinutes($duration);
                }
            }

            if (!empty($activeSlots) || !empty($inactiveSlots)) {
                $slotData = [
                    'date'            => $jalaliDate,
                    'day_name'        => $persianDayName,
                    'available_slots' => $activeSlots,
                    'available_count' => count($activeSlots),
                    'inactive_slots'  => $inactiveSlots,
                    'inactive_count'  => count($inactiveSlots),
                ];
                $slots[] = $slotData;
            }
        }

        return [
            'next_available_slot' => $nextAvailableSlot,
            'next_available_datetime' => $nextAvailableDateTime,
            'slots' => $slots,
            'max_appointments' => $maxAppointments,
        ];
    }

    private function getNextAvailableOnlineSlot($doctor, $type)
    {
        $doctorId = $doctor->id;
        $today = Carbon::today('Asia/Tehran');
        $now = Carbon::now('Asia/Tehran');
        $daysOfWeek = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $currentDayIndex = $today->dayOfWeek;

        // دریافت تنظیمات مشاوره
        $counselingConfig = DoctorCounselingConfig::where('doctor_id', $doctorId)->first();
        $autoScheduling = $counselingConfig ? $counselingConfig->auto_scheduling : 1;
        if (!$autoScheduling) {
            return [
                'next_available_slot' => null,
                'next_available_datetime' => null,
                'slots' => [],
            ];
        }
        $calendarDays = $counselingConfig->calendar_days ?? 30;
        $defaultDuration = $counselingConfig->appointment_duration ?? 15;

        // دریافت نوبت‌های رزروشده
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
        $nextAvailableDateTime = null;

        for ($i = 0; $i < $calendarDays; $i++) {
            $checkDayIndex = ($currentDayIndex + $i) % 7;
            $dayName = $daysOfWeek[$checkDayIndex];
            $checkDate = $today->copy()->addDays($i);
            $checkDateString = $checkDate->toDateString();
            $jalaliDate = Jalalian::fromCarbon($checkDate)->format('j F Y');
            $persianDayName = Jalalian::fromCarbon($checkDate)->format('l');

            // بررسی تعطیلات
            $holidays = DoctorCounselingHoliday::where('doctor_id', $doctorId)
                ->where('status', 'active')
                ->first();
            if ($holidays && $holidays->holiday_dates) {
                $holidayDates = is_string($holidays->holiday_dates) ? json_decode($holidays->holiday_dates, true) : $holidays->holiday_dates;
                if (in_array($checkDateString, $holidayDates)) {

                    continue;
                }
            }

            // بررسی برنامه روزانه خاص
            $specialSchedule = CounselingDailySchedule::where('doctor_id', $doctorId)
                ->where('date', $checkDateString)
                ->first();

            $schedule = null;
            $workHours = [];
            $emergencyTimes = [];
            $duration = $defaultDuration; // مقدار پیش‌فرض
            $maxAppointments = null;

            if ($specialSchedule) {
                $workHours = is_string($specialSchedule->consultation_hours) ? json_decode($specialSchedule->consultation_hours, true) : $specialSchedule->consultation_hours;
                $emergencyTimes = is_string($specialSchedule->emergency_times) ? json_decode($specialSchedule->emergency_times, true) : ($specialSchedule->emergency_times ?? []);
                if (!empty($workHours) && isset($workHours[0]['max_appointments'])) {
                    $maxAppointments = $workHours[0]['max_appointments'];
                }
                // بررسی appointment_settings برای max_appointments یا duration
                $appointmentSettings = is_string($specialSchedule->appointment_settings) ? json_decode($specialSchedule->appointment_settings, true) : $specialSchedule->appointment_settings;
                $duration = $this->getAppointmentDuration($appointmentSettings, $dayName, $defaultDuration);
                if (!empty($appointmentSettings) && isset($appointmentSettings[0]['max_appointments'])) {
                    $maxAppointments = $appointmentSettings[0]['max_appointments'];
                }
            } else {
                $schedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
                    ->where('day', $dayName)
                    ->where('is_working', true)
                    ->first();
                if ($schedule) {
                    $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                    $emergencyTimes = is_string($schedule->emergency_times) ? json_decode($schedule->emergency_times, true) : ($schedule->emergency_times ?? []);
                    if (!empty($workHours) && isset($workHours[0]['max_appointments'])) {
                        $maxAppointments = $workHours[0]['max_appointments'];
                    }
                    // بررسی appointment_settings برای max_appointments یا duration
                    $appointmentSettings = is_string($schedule->appointment_settings) ? json_decode($schedule->appointment_settings, true) : $schedule->appointment_settings;
                    $duration = $this->getAppointmentDuration($appointmentSettings, $dayName, $defaultDuration);
                    if (!empty($appointmentSettings) && isset($appointmentSettings['max_appointments'])) {
                        $maxAppointments = $appointmentSettings['max_appointments'];
                    }
                }
            }

            if (empty($workHours)) {

                continue;
            }

            // نرمال‌سازی emergency_times
            $normalizedEmergencyTimes = [];
            if (is_array($emergencyTimes)) {
                if (!empty($emergencyTimes) && is_array($emergencyTimes[0])) {
                    $normalizedEmergencyTimes = $emergencyTimes[0]; // فرض: [["00:00","00:21","00:42"]] → ["00:00","00:21","00:42"]
                } else {
                    $normalizedEmergencyTimes = $emergencyTimes;
                }
            }



            $dayAppointments = $bookedAppointments->filter(function ($appointment) use ($checkDate) {
                return Carbon::parse($appointment->appointment_date)->isSameDay($checkDate);
            });

            $activeSlots = [];
            $inactiveSlots = [];

            foreach ($workHours as $workHour) {
                if (!isset($workHour['start']) || !isset($workHour['end'])) {

                    continue;
                }

                $startTime = Carbon::parse($checkDateString . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime = Carbon::parse($checkDateString . ' ' . $workHour['end'], 'Asia/Tehran');

                // محاسبه appointment_duration بر اساس max_appointments، اگر در appointment_settings تنظیم نشده باشد
                if ($duration === $defaultDuration && isset($workHour['max_appointments']) && $workHour['max_appointments'] > 0) {
                    $totalMinutes = $startTime->diffInMinutes($endTime);
                    $duration = floor($totalMinutes / $workHour['max_appointments']);
                }



                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $slotTime = $currentTime->format('H:i');

                    // بررسی زمان‌های اورژانسی
                    $isEmergency = false;
                    foreach ($normalizedEmergencyTimes as $emergencyTime) {
                        try {
                            if (is_string($emergencyTime)) {
                                $emergencyTimeParsed = Carbon::parse($checkDateString . ' ' . $emergencyTime, 'Asia/Tehran');
                                if ($currentTime->format('H:i') === $emergencyTimeParsed->format('H:i')) {
                                    $isEmergency = true;
                                    break;
                                }
                            } else {

                            }
                        } catch (\Exception $e) {

                        }
                    }

                    if ($isEmergency) {
                        $inactiveSlots[] = $slotTime;
                        $currentTime->addMinutes($duration);
                        continue;
                    }

                    // بررسی رزروهای قبلی
                    $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $duration) {
                        try {
                            $apptStart = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time, 'Asia/Tehran');
                            $apptEnd = $apptStart->copy()->addMinutes($duration);
                            return $currentTime->lt($apptEnd) && $currentTime->gte($apptStart);
                        } catch (\Exception $e) {

                            return false;
                        }
                    });

                    if ($checkDate->isToday()) {
                        if ($isBooked || $currentTime->lt($now)) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                                $nextAvailableDateTime = $currentTime->toDateTimeString();
                            }
                        }
                    } else {
                        if ($isBooked) {
                            $inactiveSlots[] = $slotTime;
                        } else {
                            $activeSlots[] = $slotTime;
                            if (!$nextAvailableSlot) {
                                $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                                $nextAvailableDateTime = $currentTime->toDateTimeString();
                            }
                        }
                    }

                    $currentTime->addMinutes($duration);
                }
            }

            if (!empty($activeSlots) || !empty($inactiveSlots)) {
                $slotData = [
                    'date'            => $jalaliDate,
                    'day_name'        => $persianDayName,
                    'available_slots' => $activeSlots,
                    'available_count' => count($activeSlots),
                    'inactive_slots'  => $inactiveSlots,
                    'inactive_count'  => count($inactiveSlots),
                ];
                $slots[] = $slotData;
            }
        }

        return [
            'next_available_slot' => $nextAvailableSlot,
            'next_available_datetime' => $nextAvailableDateTime,
            'slots' => $slots,
        ];
    }

    /**
     * استخراج مدت زمان نوبت از appointment_settings برای روز مشخص
     * پشتیبانی از فرمت‌های قدیمی و جدید
     */
    private function getAppointmentDuration($appointmentSettings, $dayOfWeek, $defaultDuration = 15)
    {
        if (empty($appointmentSettings)) {
            return $defaultDuration;
        }

        // فرمت جدید: هر آیتم شامل 'day' field
        if (isset($appointmentSettings[0]['day'])) {
            // پیدا کردن تنظیمات برای روز فعلی
            foreach ($appointmentSettings as $setting) {
                if (isset($setting['day']) && $setting['day'] === $dayOfWeek) {
                    return $setting['appointment_duration'] ?? $defaultDuration;
                }
            }
            return $defaultDuration;
        }

        // فرمت قدیمی: هر آیتم شامل 'days' array
        foreach ($appointmentSettings as $setting) {
            if (isset($setting['days']) && is_array($setting['days']) && in_array($dayOfWeek, $setting['days'])) {
                return $setting['appointment_duration'] ?? $defaultDuration;
            }
        }

        return $defaultDuration;
    }
}
