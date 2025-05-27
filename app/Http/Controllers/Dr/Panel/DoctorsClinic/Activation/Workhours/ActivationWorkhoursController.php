<?php

namespace App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Workhours;

use App\Models\Clinic;
use Illuminate\Http\Request;
use App\Models\DoctorWorkSchedule;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;
use App\Models\DoctorAppointmentConfig;

class ActivationWorkhoursController extends Controller
{
    public function index($clinicId)
    {
        $doctor = Auth::guard('doctor')->user() ?? Auth::guard('secretary')->user();
        if (!$doctor) {
            return redirect()->route('dr.auth.login-register-form')->with('error', 'ابتدا وارد شوید.');
        }
        $doctorId = $doctor instanceof \App\Models\Doctor ? $doctor->id : $doctor->doctor_id;
        $hasCollaboration = DoctorAppointmentConfig::where('doctor_id', $doctorId)
            ->where('clinic_id', $clinicId)
            ->where('collaboration_with_other_sites', true)
            ->exists();

        return view('dr.panel.doctors-clinic.activation.workhours.index', compact(['clinicId', 'doctorId', 'hasCollaboration']));
    }
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id'          => 'required|exists:doctors,id',
            'clinic_id'          => 'required|exists:clinics,id',
            'day'                => 'required|array|min:1',
            'day.*'              => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'work_hours'         => 'required|array|min:1',
            'work_hours.*.start' => ['required', 'date_format:H:i'],
            'work_hours.*.end'   => ['required', 'date_format:H:i', 'after:work_hours.*.start'],
        ]);

        $appointmentDuration = DoctorAppointmentConfig::where('clinic_id', $request->clinic_id)->first()->appointment_duration;
        Log::info("Appointment Duration for clinic_id {$request->clinic_id}: {$appointmentDuration}");

        $dayTranslations = [
            'saturday'  => 'شنبه',
            'sunday'    => 'یکشنبه',
            'monday'    => 'دوشنبه',
            'tuesday'   => 'سه‌شنبه',
            'wednesday' => 'چهارشنبه',
            'thursday'  => 'پنجشنبه',
            'friday'    => 'جمعه',
        ];

        foreach ($request->day as $day) {
            $schedule = DoctorWorkSchedule::firstOrNew([
                'doctor_id' => $request->doctor_id,
                'clinic_id' => $request->clinic_id,
                'day'       => $day,
            ]);

            $existingHours = $schedule->work_hours ? json_decode($schedule->work_hours, true) : [];

            $newHours = [];
            foreach ($request->work_hours as $hour) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $hour['start']);
                $end   = \Carbon\Carbon::createFromFormat('H:i', $hour['end']);

                foreach ($existingHours as $existing) {
                    $existingStart = \Carbon\Carbon::createFromFormat('H:i', $existing['start']);
                    $existingEnd   = \Carbon\Carbon::createFromFormat('H:i', $existing['end']);

                    if ($end->lessThanOrEqualTo($existingStart) || $start->greaterThanOrEqualTo($existingEnd)) {
                        continue;
                    } else {
                        return response()->json([
                            'success' => false,
                            'message' => "بازه زمانی {$hour['start']} تا {$hour['end']} با بازه موجود {$existing['start']} تا {$existing['end']} برای روز {$dayTranslations[$day]} تداخل دارد."
                        ], 422);
                    }
                }

                // محاسبه اختلاف زمان
                $diffInMinutes = $start->diffInMinutes($end); // ترتیب رو عوض کردیم: start -> end
                if ($end->lessThan($start)) {
                    // اگر زمان پایان قبل از شروع باشه (عبور از نیمه‌شب)
                    $endForCalc = $end->copy()->addDay();
                    $diffInMinutes = $start->diffInMinutes($endForCalc);
                }

                Log::info("Diff in minutes for {$hour['start']} to {$hour['end']}: {$diffInMinutes}");

                $maxAppointments = intdiv($diffInMinutes, $appointmentDuration);
                Log::info("Max appointments for {$hour['start']} to {$hour['end']}: {$maxAppointments}");

                $newHours[] = [
                    'start'            => $hour['start'],
                    'end'              => $hour['end'],
                    'max_appointments' => $maxAppointments, // مستقیماً مقدار رو ذخیره می‌کنیم
                ];
            }

            $mergedHours = array_merge($existingHours, $newHours);
            $uniqueHours = collect($mergedHours)->unique(function ($item) {
                return $item['start'] . '-' . $item['end'];
            })->values()->toArray();

            $schedule->is_working = true;
            $schedule->work_hours = json_encode($uniqueHours, JSON_UNESCAPED_UNICODE);
            $schedule->save();
        }

        return response()->json(['success' => true, 'message' => 'ساعات کاری با موفقیت ذخیره شد.']);
    }

    public function getWorkHours($clinicId, $doctorId)
    {
        $schedules = DoctorWorkSchedule::where('clinic_id', $clinicId)
            ->where('doctor_id', $doctorId)
            ->get(['day', 'work_hours']);

        return response()->json($schedules);
    }
    public function startAppointment(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
        ]);

        // بررسی اینکه آیا ساعت کاری تعریف شده است
        $workSchedule = DoctorWorkSchedule::where('doctor_id', $request->doctor_id)
            ->where('clinic_id', $request->clinic_id)
            ->whereNotNull('work_hours')
            ->first();

        if (! $workSchedule || empty(json_decode($workSchedule->work_hours, true))) {
            return response()->json(['message' => 'پزشک گرامی شما هیچ برنامه کاری تعریف نکرده اید  لطفا ابتدا برنامه کاری  را تعریف کنید تغیرات را ذخیره کنید سپس مجدد دکمه پایان را بزنید با تشکر از شما...'], 400);
        }

        // تغییر فیلد is_active به 1
        $clinic = Clinic::findOrFail($request->clinic_id);
        $clinic->is_active = 1;
        $clinic->save();

        return response()->json(['message' => 'نوبت‌دهی شروع شد.', 'redirect_url' => route('dr-panel')]);
    }

    public function deleteWorkHours(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'days'      => 'required|array|min:1',
            'days.*'    => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start'     => ['required', 'date_format:H:i'],
            'end'       => ['required', 'date_format:H:i', 'after:start'],
        ]);

        foreach ($request->days as $day) {
            $schedule = DoctorWorkSchedule::where('doctor_id', $request->doctor_id)
                ->where('clinic_id', $request->clinic_id)
                ->where('day', $day)
                ->first();

            if ($schedule) {
                $existingHours = json_decode($schedule->work_hours, true) ?: [];
                $updatedHours  = array_filter($existingHours, function ($hour) use ($request) {
                    return ! ($hour['start'] === $request->start && $hour['end'] === $request->end);
                });

                if (empty($updatedHours)) {
                    $schedule->delete();
                } else {
                    $schedule->work_hours = json_encode(array_values($updatedHours));
                    $schedule->save();
                }
            }
        }

        return response()->json(['success' => true, 'message' => 'ساعات کاری با موفقیت حذف شد.']);
    }

}
