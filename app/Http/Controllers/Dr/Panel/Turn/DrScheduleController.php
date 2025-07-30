<?php

namespace App\Http\Controllers\Dr\Panel\Turn;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Admin;
use App\Models\SubUser;
use App\Models\Secretary;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use App\Models\Admin\Manager;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;

class DrScheduleController extends Controller
{
    public function getAuthenticatedDoctor()
    {
        if (Auth::guard('doctor')->check()) {
            return Auth::guard('doctor')->user();
        } elseif (Auth::guard('secretary')->check()) {
            $secretary = Auth::guard('secretary')->user();
            if (!$secretary->doctor) {
                throw new \Exception('منشی به هیچ دکتری متصل نیست.');
            }
            return $secretary->doctor;
        }
        throw new \Exception('کاربر احراز هویت نشده است.');
    }

    public function index(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        $selectedClinicId = $request->query('selectedClinicId');
        $filterType = $request->input('type');
        $now = Carbon::now()->format('Y-m-d');

        $appointments = Appointment::with(['doctor', 'patientable', 'insurance', 'clinic'])
            ->where('doctor_id', $doctor->id)
            ->where('appointment_date', $now);

        if (empty($filterType)) {
            $appointments->withTrashed();
        }

        if ($selectedClinicId === 'default') {
            $appointments->whereNull('medical_center_id');
        } elseif ($selectedClinicId) {
            $appointments->where('medical_center_id', $selectedClinicId);
        }

        if ($filterType) {
            if ($filterType === "in_person") {
                $appointments->where('medical_center_id', $selectedClinicId);
            } elseif ($filterType === "online") {
                $appointments->whereNull('medical_center_id');
            }
        }

        $appointments = $appointments->get();

        return view('dr.panel.turn.schedule.appointments', compact('appointments'));
    }

    public function getAppointmentsByDate(Request $request)
    {
        $selectedClinicId = $request->selectedClinicId;
        $jalaliDate = $request->input('date');

        // اعتبارسنجی اولیه
        if (empty($jalaliDate)) {
            return response()->json([
                'success' => false,
                'appointments' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
                'error' => 'تاریخ الزامی است.'
            ], 400);
        }

        // بررسی فرمت‌های مختلف تاریخ
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jalaliDate)) {
            $gregorianDate = $jalaliDate; // فرمت میلادی
        } elseif (preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $jalaliDate)) {
            try {
                $gregorianDate = Jalalian::fromFormat('Y/m/d', $jalaliDate)->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'appointments' => [],
                    'pagination' => [
                        'current_page' => 1,
                        'last_page' => 1,
                        'per_page' => 10,
                        'total' => 0,
                    ],
                    'error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'appointments' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
                'error' => 'فرمت تاریخ نامعتبر است. از فرمت Y/m/d یا Y-m-d استفاده کنید.'
            ], 400);
        }

        $doctorId = Auth::guard('doctor')->user()->id;

        $query = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $gregorianDate)
            ->with(['patientable', 'insurance']);

        if ($selectedClinicId === 'default') {
            $query->whereNull('medical_center_id');
        } elseif ($selectedClinicId) {
            $query->where('medical_center_id', $selectedClinicId);
        }

        $appointments = $query->paginate(10);

        return response()->json([
            'success' => true,
            'appointments' => $appointments->items(),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    public function searchPatients(Request $request)
    {
        $query = $request->query('query');
        $date = $request->query('date');
        $selectedClinicId = $request->query('selectedClinicId');

        if (empty($date)) {
            return response()->json([
                'success' => false,
                'patients' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
                'error' => 'تاریخ جستجو الزامی است.'
            ], 400);
        }

        if (strpos($date, '-') !== false) {
            $date = str_replace('-', '/', $date);
        }

        if (!preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            return response()->json([
                'success' => false,
                'patients' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
                'error' => 'فرمت تاریخ جلالی نادرست است.'
            ], 400);
        }

        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'patients' => [],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
                'error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'
            ], 500);
        }

        $appointmentsQuery = Appointment::with('patientable', 'insurance')
            ->whereDate('appointment_date', $gregorianDate)
            ->whereHasMorph(
                'patientable',
                [User::class, Secretary::class, Manager::class],
                function ($q) use ($query) {
                    $q->where('first_name', 'like', "%$query%")
                        ->orWhere('last_name', 'like', "%$query%")
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$query%"])
                        ->orWhere('mobile', 'like', "%$query%")
                        ->orWhere('national_code', 'like', "%$query%");
                }
            );

        if ($selectedClinicId === 'default') {
            $appointmentsQuery->whereNull('medical_center_id');
        } elseif ($selectedClinicId) {
            $appointmentsQuery->where('medical_center_id', $selectedClinicId);
        }

        $patients = $appointmentsQuery->paginate(10);

        return response()->json([
            'success' => true,
            'patients' => $patients->items(),
            'pagination' => [
                'current_page' => $patients->currentPage(),
                'last_page' => $patients->lastPage(),
                'per_page' => $patients->perPage(),
                'total' => $patients->total(),
            ],
        ]);
    }

    public function updateAppointmentDate(Request $request, $id)
    {
        $request->validate([
            'new_date' => 'required|date_format:Y-m-d',
        ]);

        $appointment = Appointment::findOrFail($id);

        if ($appointment->status === 'attended' || $appointment->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'error' => 'نمی‌توانید نوبت ویزیت‌شده یا لغو شده را جابجا کنید.'
            ], 400);
        }

        $newDate = Carbon::parse($request->new_date);
        if ($newDate->lt(Carbon::today())) {
            return response()->json([
                'success' => false,
                'error' => 'امکان جابجایی به تاریخ گذشته وجود ندارد.'
            ], 400);
        }

        $oldDate = $appointment->appointment_date;
        $appointment->appointment_date = $newDate;
        $appointment->save();

        $oldDateJalali = Jalalian::fromDateTime($oldDate)->format('Y/m/d');
        $newDateJalali = Jalalian::fromDateTime($newDate)->format('Y/m/d');

        if ($appointment->patientable && $appointment->patientable->mobile) {
            $message = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به {$newDateJalali} تغییر یافت.";
            SendSmsNotificationJob::dispatch(
                $message,
                [$appointment->patientable->mobile],
                null,
                []
            )->delay(now()->addSeconds(5));
        }

        return response()->json([
            'success' => true,
            'message' => 'نوبت با موفقیت جابجا شد.'
        ]);
    }

    public function filterAppointments(Request $request)
    {
        $status = $request->query('status');
        $attendanceStatus = $request->query('attendance_status');
        $selectedClinicId = $request->input('selectedClinicId');

        $query = Appointment::withTrashed();

        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $query->where('medical_center_id', $selectedClinicId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($attendanceStatus)) {
            $query->where('attendance_status', $attendanceStatus);
        }

        $appointments = $query->with(['patientable', 'doctor', 'clinic', 'insurance'])->paginate(10);

        return response()->json([
            'success' => true,
            'appointments' => $appointments->items(),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ],
        ]);
    }

    public function endVisit(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);

            $request->validate([
                'description' => 'nullable|string|max:1000',
            ]);

            $appointment->description = $request->input('description');
            $appointment->status = 'attended';
            $appointment->attendance_status = 'attended';
            $appointment->save();

            return response()->json([
                'success' => true,
                'message' => 'ویزیت با موفقیت ثبت شد.',
                'appointment' => $appointment
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت ویزیت: ' . $e->getMessage()
            ], 500);
        }
    }

    public function myAppointments()
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (! $doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        $subUserIds = SubUser::where('owner_id', $doctor->id)
            ->where('owner_type', \App\Models\Doctor::class)
            ->where('subuserable_type', \App\Models\User::class)
            ->pluck('subuserable_id')
            ->toArray();

        $appointments = Appointment::with(['patientable'])
            ->whereIn('patientable_id', $subUserIds)
            ->where('patientable_type', User::class)
            ->orderBy('appointment_date', 'desc')
            ->paginate(10);

        return view("dr.panel.turn.schedule.my-appointments", compact('appointments'));
    }
}
