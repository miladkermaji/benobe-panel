<?php

namespace App\Http\Controllers\Dr\Panel\DoctorsClinic\Activation\Workhours;

use Auth;
use App\Models\Dr\Clinic;
use Illuminate\Http\Request;
use App\Models\Dr\DoctorWorkSchedule;
use App\Http\Controllers\Dr\Controller;
use App\Models\Dr\DoctorAppointmentConfig;

class ActivationWorkhoursController extends Controller
{
    public function index($clinicId)
    {
        $doctorId = Auth::guard('doctor')->user()->id;
        $otherSite = DoctorAppointmentConfig::where('collaboration_with_other_sites',1)->first();
        return view('dr.panel.doctors-clinic.activation.workhours.index', compact(['clinicId', 'doctorId','otherSite']));
    }
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'clinic_id' => 'required|exists:clinics,id',
            'day' => 'required|array|min:1',
            'day.*' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'work_hours' => 'required|array|min:1',
            'work_hours.*.start' => ['required', 'date_format:H:i'],
            'work_hours.*.end' => ['required', 'date_format:H:i', 'after:work_hours.*.start'],
        ]);

        $appointmentDuration = DoctorAppointmentConfig::where('clinic_id', $request->clinic_id)->first()->appointment_duration;

        foreach ($request->day as $day) {
            $schedule = DoctorWorkSchedule::firstOrNew([
                'doctor_id' => $request->doctor_id,
                'clinic_id' => $request->clinic_id,
                'day' => $day,
            ]);

            $existingHours = $schedule->work_hours ? json_decode($schedule->work_hours, true) : [];

            $newHours = [];
            foreach ($request->work_hours as $hour) {
                $start = \Carbon\Carbon::createFromFormat('H:i', $hour['start']);
                $end = \Carbon\Carbon::createFromFormat('H:i', $hour['end']);

                // بررسی همه شرایط زمانی:
                if ($end->lessThan($start)) {
                    // اگر زمان پایان کوچکتر از شروع بود (عبور از نیمه‌شب)
                    $end = $end->addDay(); // روز بعد برای زمان پایان
                }

                // محاسبه دقیق اختلاف دقیقه‌ها
                $diffInMinutes = $end->diffInMinutes($start);

                // اطمینان از مثبت بودن اختلاف (اگر باز هم مشکلی بود)
                if ($diffInMinutes < 0) {
                    $diffInMinutes = 1440 - $start->diffInMinutes($end->copy()->subDay());
                }

                $maxAppointments = intdiv($diffInMinutes, $appointmentDuration);

                $newHours[] = [
                    'start' => $hour['start'],
                    'end' => $hour['end'],
                    'max_appointments' => max($maxAppointments, 0), // اطمینان از عدم منفی بودن
                ];
            }




            // ترکیب و حذف ساعات تکراری
            $mergedHours = array_merge($existingHours, $newHours);
            $uniqueHours = collect($mergedHours)->unique()->values()->toArray();

            $schedule->is_working = true;
            $schedule->work_hours = json_encode($uniqueHours, JSON_UNESCAPED_UNICODE); // ذخیره به صورت JSON
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
        $workHours = DoctorWorkSchedule::where('doctor_id', $request->doctor_id)
            ->where('clinic_id', $request->clinic_id)
            ->exists();

        if (!$workHours) {
            return response()->json(['message' => 'ابتدا برنامه ساعت کاری را تعریف کنید.'], 400);
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
            'days' => 'required|array|min:1',
            'days.*' => 'required|in:saturday,sunday,monday,tuesday,wednesday,thursday,friday',
            'start' => ['required', 'date_format:H:i'],
            'end' => ['required', 'date_format:H:i', 'after:start'],
        ]);

        foreach ($request->days as $day) {
            $schedule = DoctorWorkSchedule::where('doctor_id', $request->doctor_id)
                ->where('clinic_id', $request->clinic_id)
                ->where('day', $day)
                ->first();

            if ($schedule) {
                $existingHours = json_decode($schedule->work_hours, true) ?: [];
                $updatedHours = array_filter($existingHours, function ($hour) use ($request) {
                    return !($hour['start'] === $request->start && $hour['end'] === $request->end);
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
