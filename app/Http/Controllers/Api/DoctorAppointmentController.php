<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorNote;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\DoctorHoliday;
use App\Models\DoctorHolidays;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\SpecialDailySchedule;
use App\Models\CounselingAppointment;
use App\Models\SpecialDailySchedules;
use App\Models\DoctorCounselingConfig;
use App\Models\CounselingDailySchedule;
use App\Models\DoctorCounselingHoliday;
use App\Models\CounselingDailySchedules;
use App\Models\DoctorCounselingHolidays;
use App\Models\DoctorCounselingWorkSchedule;

class DoctorAppointmentController extends Controller
{
    public function getAppointmentOptions(Request $request, $doctorId)
    {
        try {
            $doctor = Doctor::with(['province', 'city', 'specialty'])->find($doctorId);
            if (!$doctor) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'پزشک یافت نشد',
                    'data'    => null,
                ], 404);
            }

            $clinics = Clinic::with(['province', 'city'])
                ->where('doctor_id', $doctorId)
                ->where('is_active', true)
                ->select('id', 'name', 'province_id', 'city_id', 'address', 'phone_number', 'is_main_clinic')
                ->get();

            $selectedClinicId = $request->query('clinic_id');
            $selectedClinic = null;
            if ($selectedClinicId) {
                if (!is_numeric($selectedClinicId) || $selectedClinicId <= 0) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'شناسه کلینیک نامعتبر است',
                        'data'    => null,
                    ], 400);
                }

                $selectedClinic = $clinics->where('id', (int)$selectedClinicId)->first();
                if (!$selectedClinic) {
                    Log::warning("GetAppointmentOptions - Clinic not found or does not belong to doctor", [
                        'doctor_id' => $doctorId,
                        'clinic_id' => $selectedClinicId,
                        'clinics_count' => $clinics->count(),
                    ]);
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کلینیک مورد نظر یافت نشد یا به این پزشک تعلق ندارد',
                        'data'    => null,
                    ], 404);
                }
            }

            $inPersonData = $selectedClinic
                ? $this->getInPersonAppointmentData($doctor, $selectedClinic)
                : $this->getInPersonAppointmentDataForAllClinics($doctor, $clinics);
            $onlineData = $this->getOnlineAppointmentData($doctor);

            return response()->json([
                'status' => 'success',
                'data'   => [
                    'doctor' => [
                        'name'        => $doctor->full_name,
                        'slug'        => $doctor->slug,
                        'specialty'   => $doctor->specialty ? $doctor->specialty->name : 'نامشخص',
                        'province'    => $doctor->province ? $doctor->province->name : null,
                        'city'        => $doctor->city ? $doctor->city->name : null,
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
            Log::error('GetAppointmentOptions - Error: ' . $e->getMessage(), [
                'doctor_id' => $doctorId,
                'request'   => $request->all(),
                'exception' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور. لطفاً دوباره تلاش کنید.',
                'data'    => null,
            ], 500);
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
        $appointmentConfig = $doctor->appointmentConfig;
        $calendarDays = $appointmentConfig ? ($appointmentConfig->calendar_days ?? 30) : 30;
        $defaultDuration = $appointmentConfig ? ($appointmentConfig->appointment_duration ?? 15) : 15;

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
                    Log::warning("GetNextAvailableSlot - Date {$checkDateString} is a holiday for doctor {$doctorId}");
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
                Log::warning("GetNextAvailableSlot - No work hours for doctor {$doctorId} on {$dayName}");
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

            Log::debug("GetNextAvailableSlot - Work hours and max appointments", [
                'doctor_id' => $doctorId,
                'clinic_id' => $clinicId,
                'day' => $dayName,
                'work_hours' => $workHours,
                'max_appointments' => $maxAppointments,
            ]);

            Log::debug("GetNextAvailableSlot - Normalized emergency times", [
                'doctor_id' => $doctorId,
                'clinic_id' => $clinicId,
                'day' => $dayName,
                'original_emergency_times' => $emergencyTimes,
                'normalized_emergency_times' => $normalizedEmergencyTimes,
            ]);

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
                        Log::warning("Invalid appointment_time format detected, corrected", [
                            'appointment_id' => $appointment->id,
                            'original_time' => $time,
                            'corrected_time' => $correctedTime,
                        ]);
                        $time = $correctedTime;
                    } elseif (!preg_match('/\d{2}:\d{2}:\d{2}/', $time)) {
                        Log::error("Invalid appointment_time format", [
                            'appointment_id' => $appointment->id,
                            'appointment_time' => $time,
                        ]);
                        continue;
                    }
                    $bookedTimes[] = $time;
                } catch (\Exception $e) {
                    Log::error("Error parsing appointment time", [
                        'appointment_id' => $appointment->id,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $activeSlots = [];
            $inactiveSlots = [];

            foreach ($workHours as $workHour) {
                if (!isset($workHour['start']) || !isset($workHour['end'])) {
                    Log::warning("Invalid work_hours structure", [
                        'doctor_id' => $doctorId,
                        'clinic_id' => $clinicId,
                        'day' => $dayName,
                        'work_hour' => $workHour,
                    ]);
                    continue;
                }

                $startTime = Carbon::parse($checkDateString . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime = Carbon::parse($checkDateString . ' ' . $workHour['end'], 'Asia/Tehran');

                // محاسبه appointment_duration بر اساس max_appointments
                if (isset($workHour['max_appointments']) && $workHour['max_appointments'] > 0) {
                    $totalMinutes = $startTime->diffInMinutes($endTime);
                    $duration = floor($totalMinutes / $workHour['max_appointments']);
                }

                Log::debug("GetNextAvailableSlot - Calculated duration", [
                    'doctor_id' => $doctorId,
                    'clinic_id' => $clinicId,
                    'day' => $dayName,
                    'total_minutes' => $startTime->diffInMinutes($endTime),
                    'max_appointments' => $workHour['max_appointments'] ?? null,
                    'duration' => $duration,
                ]);

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
                                Log::warning("Invalid emergency_time format after normalization", [
                                    'doctor_id' => $doctorId,
                                    'clinic_id' => $clinicId,
                                    'day' => $dayName,
                                    'emergency_time' => $emergencyTime,
                                ]);
                            }
                        } catch (\Exception $e) {
                            Log::error("Error parsing emergency_time", [
                                'doctor_id' => $doctorId,
                                'clinic_id' => $clinicId,
                                'day' => $dayName,
                                'emergency_time' => $emergencyTime,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }

                    if ($isEmergency) {
                        $inactiveSlots[] = $slotTime;
                        $currentTime->addMinutes($duration);
                        continue;
                    }

                    // بررسی رزروهای قبلی
                    $isBooked = in_array($slotTime, array_map(function ($time) {
                        return substr($time, 0, 5); // فقط ساعت و دقیقه
                    }, $bookedTimes));

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

        $counselingConfig = DoctorCounselingConfig::where('doctor_id', $doctorId)->first();
        $calendarDays = $counselingConfig ? ($counselingConfig->calendar_days ?? 30) : 30;
        $duration = $counselingConfig ? ($counselingConfig->appointment_duration ?? 15) : 15;

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
                    Log::warning("GetNextAvailableOnlineSlot - Date {$checkDateString} is a counseling holiday for doctor {$doctorId}");
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
            if ($specialSchedule) {
                $workHours = is_string($specialSchedule->consultation_hours) ? json_decode($specialSchedule->consultation_hours, true) : $specialSchedule->consultation_hours;
                // emergency_times برای CounselingDailySchedules تعریف نشده، پس نادیده گرفته می‌شه
            } else {
                $schedule = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
                    ->where('day', $dayName)
                    ->where('is_working', true)
                    ->first();
                if ($schedule) {
                    $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
                    $emergencyTimes = is_string($schedule->emergency_times) ? json_decode($schedule->emergency_times, true) : ($schedule->emergency_times ?? []);
                }
            }

            if (empty($workHours)) {
                Log::warning("GetNextAvailableOnlineSlot - No work hours for doctor {$doctorId} on {$dayName}");
                continue;
            }

            $dayAppointments = $bookedAppointments->filter(function ($appointment) use ($checkDate) {
                return Carbon::parse($appointment->appointment_date)->isSameDay($checkDate);
            });
            $activeSlots = [];
            $inactiveSlots = [];

            foreach ($workHours as $workHour) {
                $startTime = Carbon::parse($checkDateString . ' ' . $workHour['start'], 'Asia/Tehran');
                $endTime = Carbon::parse($checkDateString . ' ' . $workHour['end'], 'Asia/Tehran');

                $currentTime = $startTime->copy();
                while ($currentTime->lessThan($endTime)) {
                    $nextTime = $currentTime->copy()->addMinutes($duration);
                    $slotTime = $currentTime->format('H:i');

                    // بررسی زمان‌های اورژانسی
                    $isEmergency = false;
                    foreach ($emergencyTimes as $emergencyTime) {
                        if (is_array($emergencyTime)) {
                            $emergencyStart = Carbon::parse($checkDateString . ' ' . $emergencyTime['start'], 'Asia/Tehran');
                            $emergencyEnd = Carbon::parse($checkDateString . ' ' . $emergencyTime['end'], 'Asia/Tehran');
                            if ($currentTime->between($emergencyStart, $emergencyEnd)) {
                                $isEmergency = true;
                                break;
                            }
                        } else {
                            $emergencyTimeParsed = Carbon::parse($checkDateString . ' ' . $emergencyTime, 'Asia/Tehran');
                            if ($currentTime->equalTo($emergencyTimeParsed)) {
                                $isEmergency = true;
                                break;
                            }
                        }
                    }
                    if ($isEmergency) {
                        $inactiveSlots[] = $slotTime;
                        $currentTime->addMinutes($duration);
                        continue;
                    }

                    // بررسی رزروهای قبلی
                    $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
                        $apptStart = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time, 'Asia/Tehran');
                        $apptEnd = $apptStart->copy()->addMinutes($duration);
                        return $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);
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
}
