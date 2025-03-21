<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;

class DoctorListingController extends Controller
{
    public function getDoctors(Request $request)
    {
        try {
            $provinceId  = $request->input('province_id');
            $specialtyId = $request->input('specialty_id');
            $limit       = $request->input('limit', 10);
            $page        = $request->input('page', 1);
            $sort        = $request->input('sort', 'rating_desc');

            $cacheKey = "doctors_list_{$provinceId}_{$specialtyId}_{$limit}_{$page}_{$sort}";

            $doctors = Cache::remember($cacheKey, 300, function () use ($provinceId, $specialtyId, $limit, $page, $sort) {
                $query = Doctor::query()
                    ->where('status', true)
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
                        $query->orderBy('id', 'asc');
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
                $tags[] = 'خوش برخورد';
                if ($doctor->clinics->pluck('payment_methods')->contains('online')) {
                    $tags[] = 'پوشش بیمه';
                }

                $services = ['نوبت‌دهی مطب'];
                if ($doctor->appointmentConfig && $doctor->appointmentConfig->online_consultation) {
                    $services[] = 'مشاوره تلفنی';
                    $services[] = 'مشاوره متنی';
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
            return response()->json([
                'status'  => 'error',
                'message' => 'خطای سرور',
                'data'    => null,
            ], 500);
        }
    }

    /**
     * محاسبه اولین نوبت خالی برای یک پزشک (کپی از DoctorController)
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
