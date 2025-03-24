<?php

namespace App\Http\Controllers\Dr\Panel\Turn;

use App\Http\Controllers\Dr\Controller;
use App\Models\Appointment;
use App\Models\SubUser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Morilog\Jalali\Jalalian;

class DrScheduleController extends Controller
{
    public function getAuthenticatedDoctor()
    {
        $doctor = Auth::guard('doctor')->user();
        if (! $doctor) {
            $secretary = Auth::guard('secretary')->user();
            if ($secretary && $secretary->doctor) {
                $doctor = $secretary->doctor;
            }
        }
        return $doctor;
    }

    public function index(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (! $doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        $clinics = $doctor->clinics()->where('is_active', 0)->get();
        $now = Carbon::now()->format('Y-m-d');
        $selectedClinicId = $request->query('selectedClinicId');
        $filterType = $request->input('type');

        $appointments = Appointment::with(['doctor', 'patient', 'insurance', 'clinic'])
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $now);

        if (empty($filterType)) {
            $appointments->withTrashed();
        }

        if ($selectedClinicId === 'default') {
            $appointments->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            $appointments->where('clinic_id', $selectedClinicId);
        }

        if ($filterType) {
            if ($filterType === "in_person") {
                $appointments->where('clinic_id', $selectedClinicId);
            } elseif ($filterType === "online") {
                $appointments->whereNull('clinic_id');
            }
        }

        $appointments = $appointments->get();

        if ($request->ajax()) {
            return response()->json([
                'appointments' => $appointments,
                'clinics' => $clinics,
            ]);
        }

        return view("dr.panel.turn.schedule.appointments", compact('appointments', 'clinics'));
    }

    public function filterAppointments(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (! $doctor) {
            return response()->json(['error' => 'دسترسی غیرمجاز!'], 403);
        }

        $selectedClinicId = $request->query('selectedClinicId');
        $filterType = $request->input('type');
        $selectedDate = $request->input('date');

        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $selectedDate)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'فرمت تاریخ نامعتبر است.'], 400);
        }

        $query = Appointment::with(['patient', 'clinic'])
            ->where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $gregorianDate);

        if (empty($filterType)) {
            $query->withTrashed();
        }

        if ($selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            $query->where('clinic_id', $selectedClinicId);
        }

        if ($filterType) {
            if ($filterType === "in_person") {
                $query->where('clinic_id', $selectedClinicId);
            }
        }

        $appointments = $query->get();

        return response()->json(['appointments' => $appointments]);
    }

  public function showByDateAppointments(Request $request)
{
    $doctor = $this->getAuthenticatedDoctor();
    if (! $doctor) {
        abort(403, 'شما به این بخش دسترسی ندارید.');
    }

    $selectedDate = $request->input('date', Jalalian::now()->format('Y/m/d'));
    $selectedClinicId = $request->query('selectedClinicId');
    $filterType = $request->input('type');

    try {
        $gregorianDate = Jalalian::fromFormat('Y/m/d', $selectedDate)->toCarbon()->format('Y-m-d');
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'فرمت تاریخ وارد شده نامعتبر است.',
        ], 400);
    }

    $appointments = Appointment::with(['doctor', 'patient', 'insurance', 'clinic'])
        ->where('doctor_id', $doctor->id)
        ->where('appointment_date', '=', $gregorianDate);

    if (empty($filterType)) {
        $appointments->withTrashed(); // برای "کل نوبت‌ها" نوبت‌های ترش‌شده رو هم بیار
    }

    if ($selectedClinicId === 'default') {
        $appointments->whereNull('clinic_id');
    } elseif ($selectedClinicId) {
        $appointments->where('clinic_id', $selectedClinicId);
    }

    if ($filterType) {
        if ($filterType === "in_person") {
            $appointments->whereNotNull('clinic_id')
                         ->where('clinic_id', $selectedClinicId);
        } elseif ($filterType === "online") {
            $appointments->whereNull('clinic_id'); // فقط نوبت‌های آنلاین بدون کلینیک
        }
    }

    $appointments = $appointments->get();

    return response()->json([
        'appointments' => $appointments,
    ]);
}

public function searchAppointments(Request $request)
{
    $doctor = $this->getAuthenticatedDoctor();
    if (!$doctor) {
        return response()->json(['error' => 'دسترسی غیرمجاز!'], 403);
    }

    $searchQuery = $request->input('query');
    $selectedClinicId = $request->query('selectedClinicId');
    $filterType = $request->input('type');
    $date = $request->input('date', Jalalian::now()->format('Y/m/d'));

    try {
        $gregorianDate = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
    } catch (\Exception $e) {
        return response()->json(['error' => 'فرمت تاریخ نامعتبر است.'], 400);
    }

    $query = Appointment::with(['patient', 'clinic'])
        ->where('doctor_id', $doctor->id)
        ->where('appointment_date', $gregorianDate);

    // برای "کل نوبت‌ها" نوبت‌های ترش‌شده رو هم بیار
    if (empty($filterType)) {
        $query->withTrashed();
    }

    // اعمال فیلتر کلینیک
    if ($selectedClinicId === 'default') {
        $query->whereNull('clinic_id');
    } elseif ($selectedClinicId) {
        $query->where('clinic_id', $selectedClinicId);
    }

    // اعمال فیلتر نوع نوبت
    if ($filterType) {
        if ($filterType === 'in_person') {
            $query->whereNotNull('clinic_id')
                  ->where('clinic_id', $selectedClinicId);
        } elseif ($filterType === 'online') {
            $query->whereNull('clinic_id'); // فقط نوبت‌های آنلاین بدون کلینیک
        }
    }

    // اعمال جستجو
    if ($searchQuery) {
        $query->where(function ($q) use ($searchQuery) {
            $q->whereHas('patient', function ($patientQuery) use ($searchQuery) {
                $patientQuery->where('first_name', 'like', "%{$searchQuery}%")
                    ->orWhere('last_name', 'like', "%{$searchQuery}%")
                    ->orWhere('mobile', 'like', "%{$searchQuery}%")
                    ->orWhere('national_code', 'like', "%{$searchQuery}%");
            });
        });
    }

    $appointments = $query->get();

    return response()->json(['appointments' => $appointments]);
}

    public function endVisit(Request $request, $id)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            return response()->json(['error' => 'دسترسی غیرمجاز!'], 403);
        }

        $appointment = Appointment::where('doctor_id', $doctor->id)->findOrFail($id);

        if ($appointment->status === 'attended' || $appointment->status === 'cancelled') {
            return response()->json(['error' => 'این نوبت قبلاً پایان یافته یا لغو شده است.'], 400);
        }

        $appointment->status = 'attended';
        $appointment->save();

        return response()->json(['message' => 'ویزیت با موفقیت پایان یافت.']);
    }

    public function myAppointments()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (! $doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        $subUserIds = SubUser::where('doctor_id', $doctor->id)
            ->pluck('user_id')
            ->toArray();

        $appointments = Appointment::with(['patient'])
            ->whereIn('patient_id', $subUserIds)
            ->orderBy('appointment_date', 'desc')
            ->paginate(10);

        return view("dr.panel.turn.schedule.my-appointments", compact('appointments'));
    }
}
