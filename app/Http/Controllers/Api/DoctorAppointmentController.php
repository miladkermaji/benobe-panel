<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\CounselingAppointment;
use App\Models\Doctor;
use App\Models\DoctorCounselingConfig;
use App\Models\DoctorCounselingWorkSchedule;
use App\Models\DoctorNote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Morilog\Jalali\Jalalian;

class DoctorAppointmentController extends Controller
{
    public function getAppointmentOptions(Request $request, $doctorId)
    {
        try {
            $doctor = Doctor::with(['province', 'city', 'specialty'])->find($doctorId);
            if (! $doctor) {
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
            $selectedClinic   = null;
            if ($selectedClinicId) {
                $selectedClinic = $clinics->where('id', (int) $selectedClinicId)->first();
                if (! $selectedClinic) {
                    return response()->json([
                        'status'  => 'error',
                        'message' => 'کلینیک یافت نشد یا متعلق به این پزشک نیست',
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
                    'doctor'            => [
                        'name'        => $doctor->full_name,
                        'specialty'   => $doctor->specialty ? $doctor->specialty->name : 'نامشخص',
                        'province'    => $doctor->province ? $doctor->province->name : null,
                        'city'        => $doctor->city ? $doctor->city->name : null,
                        'views_count' => $doctor->views_count ?? 0,
                        'rating'      => $doctor->rating ?? 0,
                    ],
                    'clinics'           => $clinics->map(function ($clinic) {
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
                    'selected_clinic'   => $selectedClinic ? [
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
            Log::error('GetAppointmentOptions - Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    private function getInPersonAppointmentDataForAllClinics($doctor, $clinics)
    {
        $data = [
            'next_available_slot' => null,
            'clinics'             => [],
        ];

        foreach ($clinics as $clinic) {
            $slotData = $this->getNextAvailableSlot($doctor, $clinic->id);
            $notes    = DoctorNote::where('doctor_id', $doctor->id)
                ->where('clinic_id', $clinic->id)
                ->where('appointment_type', 'in_person')
                ->first();

            if ($slotData['next_available_slot']) {
                $data['clinics'][] = [
                    'clinic_id'           => $clinic->id,
                    'name'                => $clinic->name,
                    'province'            => $clinic->province ? $clinic->province->name : null,
                    'city'                => $clinic->city ? $clinic->city->name : null,
                    'address'             => $clinic->address,
                    'notes'               => $notes ? $notes->notes : 'ملاحظات خاصی برای این نوبت ثبت نشده است',
                    'next_available_slot' => $slotData['next_available_slot'],
                    'slots'               => $slotData['slots'],
                ];
                if (! $data['next_available_slot'] || Carbon::parse($slotData['next_available_slot'])->lt(Carbon::parse($data['next_available_slot']))) {
                    $data['next_available_slot'] = $slotData['next_available_slot'];
                }
            }
        }

        return $data;
    }

    private function getInPersonAppointmentData($doctor, $clinic)
    {
        $slotData = $this->getNextAvailableSlot($doctor, $clinic->id);
        $notes    = DoctorNote::where('doctor_id', $doctor->id)
            ->where('clinic_id', $clinic->id)
            ->where('appointment_type', 'in_person')
            ->first();

        return [
            'next_available_slot' => $slotData['next_available_slot'],
            'clinic'              => [
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
        $fee              = $counselingConfig ? ($counselingConfig->price_15min ?? 100000) : 100000;

        $onlineTypes = [
            ['type' => 'phone', 'name' => 'مشاوره تلفنی', 'fee' => $fee, 'appointment_type' => 'online_phone'],
            ['type' => 'text', 'name' => 'مشاوره متنی', 'fee' => $fee, 'appointment_type' => 'online_text'],
        ];

        $data = ['types' => []];
        foreach ($onlineTypes as $type) {
            $slotData = $this->getNextAvailableOnlineSlot($doctor, $type['type']);
            $notes    = DoctorNote::where('doctor_id', $doctor->id)
                ->where('appointment_type', $type['appointment_type'])
                ->first();
            $data['types'][] = [
                'type'                => $type['type'],
                'name'                => $type['name'],
                'fee'                 => $type['fee'],
                'notes'               => $notes ? $notes->notes : 'ملاحظات خاصی برای این نوع مشاوره ثبت نشده است',
                'next_available_slot' => $slotData['next_available_slot'],
                'slots'               => $slotData['slots'],
            ];
        }

        return $data;
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
        ->orwhere('status', 'pending_review')
        ->where('appointment_date', '>=', $today->toDateString())
        ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
        ->get()
        ->groupBy(function ($appointment) {
        return Carbon::parse($appointment->appointment_date)->toDateString();
    });
    Log::debug("GetNextAvailableSlot - Booked appointments: ", ['bookedAppointments' => $bookedAppointments->toArray()]);

    $slots             = [];
    $nextAvailableSlot = null;

    for ($i = 0; $i < $calendarDays; $i++) {
        $checkDayIndex  = ($currentDayIndex + $i) % 7;
        $dayName        = $daysOfWeek[$checkDayIndex];
        $checkDate      = $today->copy()->addDays($i);
        $jalaliDate     = Jalalian::fromCarbon($checkDate)->format('j F Y');
        $persianDayName = Jalalian::fromCarbon($checkDate)->format('l');

        Log::debug("GetNextAvailableSlot - Checking date: {$checkDate->toDateString()}", [
            'dayName' => $dayName,
            'jalaliDate' => $jalaliDate,
            'persianDayName' => $persianDayName,
        ]);

        $dayAppointments = $bookedAppointments->get($checkDate->toDateString(), collect());
        Log::debug("GetNextAvailableSlot - Day appointments for {$checkDate->toDateString()}: ", ['dayAppointments' => $dayAppointments->toArray()]);

        $activeSlots     = [];
        $inactiveSlots   = [];

        foreach ($schedules as $schedule) {
            if ($schedule->day !== $dayName) {
                Log::debug("GetNextAvailableSlot - Skipping schedule for day {$schedule->day}, current day: {$dayName}");
                continue;
            }

            $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
            if (! is_array($workHours) || empty($workHours)) {
                Log::warning("GetNextAvailableSlot - Invalid work hours for schedule: ", ['schedule' => $schedule->toArray()]);
                continue;
            }
            $workHour = $workHours[0];
            Log::debug("GetNextAvailableSlot - Work hours: ", ['workHour' => $workHour]);

            $startTime = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['start'], 'Asia/Tehran');
            $endTime   = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['end'], 'Asia/Tehran');
            Log::debug("GetNextAvailableSlot - Time range: ", [
                'startTime' => $startTime->toDateTimeString(),
                'endTime' => $endTime->toDateTimeString(),
            ]);

            $currentTime = $startTime->copy();
            while ($currentTime->lessThan($endTime)) {
                $nextTime = (clone $currentTime)->addMinutes($duration);
                $slotTime = $currentTime->format('H:i');

$isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
    $dateOnly = Carbon::parse($appointment->appointment_date)->toDateString();
    // فقط بخش زمان رو از appointment_time بگیریم
    $timeOnly = Carbon::parse($appointment->appointment_time)->format('H:i:s');
    $combinedDateTime = $dateOnly . ' ' . $timeOnly;
    Log::debug("GetNextAvailableSlot - Combined date and time: ", ['combined' => $combinedDateTime]);
    $apptStart = Carbon::parse($combinedDateTime, 'Asia/Tehran');
    $apptEnd   = (clone $apptStart)->addMinutes($duration);
    $isOverlapping = $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);
    Log::debug("GetNextAvailableSlot - Checking slot {$currentTime->format('H:i')} for overlap: ", [
        'appointment_start' => $apptStart->toDateTimeString(),
        'appointment_end' => $apptEnd->toDateTimeString(),
        'slot_start' => $currentTime->toDateTimeString(),
        'slot_end' => $nextTime->toDateTimeString(),
        'is_overlapping' => $isOverlapping,
    ]);
    return $isOverlapping;
});

                if ($checkDate->isToday()) {
                    if ($isBooked || $currentTime->lt($now)) {
                        $inactiveSlots[] = $slotTime;
                    } else {
                        $activeSlots[] = $slotTime;
                        if (! $nextAvailableSlot) {
                            $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                        }
                    }
                } else {
                    if ($isBooked) {
                        $inactiveSlots[] = $slotTime;
                    } else {
                        $activeSlots[] = $slotTime;
                        if (! $nextAvailableSlot) {
                            $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
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
                'inactive_slots'  => $inactiveSlots,
                'inactive_count'  => count($inactiveSlots),
            ];
            $slots[] = $slotData;
        }
    }

    Log::debug("GetNextAvailableSlot - Final result: ", [
        'next_available_slot' => $nextAvailableSlot,
        'slots' => $slots,
    ]);

    return [
        'next_available_slot' => $nextAvailableSlot,
        'slots'               => $slots,
        'max_appointments'    => $schedules->first()->appointment_settings[0]['max_appointments'] ?? 22,
    ];
}

private function getNextAvailableOnlineSlot($doctor, $type)
{
    $doctorId        = $doctor->id;
    $today           = Carbon::today('Asia/Tehran');
    $now             = Carbon::now('Asia/Tehran');
    $daysOfWeek      = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
    $currentDayIndex = $today->dayOfWeek;

    Log::debug("GetNextAvailableOnlineSlot - Starting for doctor {$doctorId}, type {$type}", [
        'today' => $today->toDateString(),
        'now' => $now->toDateTimeString(),
        'currentDayIndex' => $currentDayIndex,
    ]);

    $counselingConfig = DoctorCounselingConfig::where('doctor_id', $doctorId)->first();
    $calendarDays     = $counselingConfig ? ($counselingConfig->calendar_days ?? 30) : 30;
    $duration         = $counselingConfig ? ($counselingConfig->appointment_duration ?? 15) : 15;

    $schedules = DoctorCounselingWorkSchedule::where('doctor_id', $doctorId)
        ->where('is_working', true)
        ->get();
    if ($schedules->isEmpty()) {
        Log::warning("GetNextAvailableOnlineSlot - No schedules found for doctor {$doctorId}");
        return ['next_available_slot' => null, 'slots' => []];
    }
    Log::debug("GetNextAvailableOnlineSlot - Schedules found: ", ['schedules' => $schedules->toArray()]);

    $bookedAppointments = CounselingAppointment::where('doctor_id', $doctorId)
        ->where('appointment_type', $type)
        ->where('status', 'scheduled')
        ->orWhere('status', 'pending_review')
        
        ->where('appointment_date', '>=', $today->toDateString())
        ->where('appointment_date', '<=', $today->copy()->addDays($calendarDays)->toDateString())
        ->get()
        ->groupBy('appointment_date');
    Log::debug("GetNextAvailableOnlineSlot - Booked appointments: ", ['bookedAppointments' => $bookedAppointments->toArray()]);

    $slots             = [];
    $nextAvailableSlot = null;

    for ($i = 0; $i < $calendarDays; $i++) {
        $checkDayIndex  = ($currentDayIndex + $i) % 7;
        $dayName        = $daysOfWeek[$checkDayIndex];
        $checkDate      = $today->copy()->addDays($i);
        $jalaliDate     = Jalalian::fromCarbon($checkDate)->format('j F Y');
        $persianDayName = Jalalian::fromCarbon($checkDate)->format('l');

        Log::debug("GetNextAvailableOnlineSlot - Checking date: {$checkDate->toDateString()}", [
            'dayName' => $dayName,
            'jalaliDate' => $jalaliDate,
            'persianDayName' => $persianDayName,
        ]);

        $dayAppointments = $bookedAppointments->get($checkDate->toDateString(), collect());
        Log::debug("GetNextAvailableOnlineSlot - Day appointments for {$checkDate->toDateString()}: ", ['dayAppointments' => $dayAppointments->toArray()]);

        $activeSlots     = [];
        $inactiveSlots   = [];

        foreach ($schedules as $schedule) {
            if ($schedule->day !== $dayName) {
                Log::debug("GetNextAvailableOnlineSlot - Skipping schedule for day {$schedule->day}, current day: {$dayName}");
                continue;
            }

            $workHours = is_string($schedule->work_hours) ? json_decode($schedule->work_hours, true) : $schedule->work_hours;
            if (! is_array($workHours) || empty($workHours)) {
                Log::warning("GetNextAvailableOnlineSlot - Invalid work hours for schedule: ", ['schedule' => $schedule->toArray()]);
                continue;
            }
            $workHour = $workHours[0];
            Log::debug("GetNextAvailableOnlineSlot - Work hours: ", ['workHour' => $workHour]);

            $startTime = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['start'], 'Asia/Tehran');
            $endTime   = Carbon::parse($checkDate->toDateString() . ' ' . $workHour['end'], 'Asia/Tehran');
            Log::debug("GetNextAvailableOnlineSlot - Time range: ", [
                'startTime' => $startTime->toDateTimeString(),
                'endTime' => $endTime->toDateTimeString(),
            ]);

            $currentTime = $startTime->copy();
            while ($currentTime->lessThan($endTime)) {
                $nextTime = (clone $currentTime)->addMinutes($duration);
                $slotTime = $currentTime->format('H:i');

                $isBooked = $dayAppointments->contains(function ($appointment) use ($currentTime, $nextTime, $duration) {
                    $apptStart = Carbon::parse($appointment->appointment_date . ' ' . $appointment->appointment_time, 'Asia/Tehran');
                    $apptEnd   = (clone $apptStart)->addMinutes($duration);
                    $isOverlapping = $currentTime->lt($apptEnd) && $nextTime->gt($apptStart);
                    Log::debug("GetNextAvailableOnlineSlot - Checking slot {$currentTime->format('H:i')} for overlap: ", [
                        'appointment_start' => $apptStart->toDateTimeString(),
                        'appointment_end' => $apptEnd->toDateTimeString(),
                        'slot_start' => $currentTime->toDateTimeString(),
                        'slot_end' => $nextTime->toDateTimeString(),
                        'is_overlapping' => $isOverlapping,
                    ]);
                    return $isOverlapping;
                });

                if ($checkDate->isToday()) {
                    if ($isBooked || $currentTime->lt($now)) {
                        $inactiveSlots[] = $slotTime;
                    } else {
                        $activeSlots[] = $slotTime;
                        if (! $nextAvailableSlot) {
                            $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
                        }
                    }
                } else {
                    if ($isBooked) {
                        $inactiveSlots[] = $slotTime;
                    } else {
                        $activeSlots[] = $slotTime;
                        if (! $nextAvailableSlot) {
                            $nextAvailableSlot = "$jalaliDate ساعت $slotTime";
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
                'inactive_slots'  => $inactiveSlots,
                'inactive_count'  => count($inactiveSlots),
            ];
            $slots[] = $slotData;
        }
    }

    Log::debug("GetNextAvailableOnlineSlot - Final result: ", [
        'next_available_slot' => $nextAvailableSlot,
        'slots' => $slots,
    ]);

    return [
        'next_available_slot' => $nextAvailableSlot,
        'slots'               => $slots,
    ];
}
}
