<?php

namespace App\Http\Controllers\Mc\Panel\Turn;

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
use App\Http\Controllers\Mc\Controller;

class DrScheduleController extends Controller
{
    public function getAuthenticatedDoctor()
    {
        if (Auth::guard('medical_center')->check()) {
            $medicalCenter = Auth::guard('medical_center')->user();
            $selectedDoctorId = $medicalCenter->selectedDoctor?->doctor_id;

            if (!$selectedDoctorId) {
                throw new \Exception('هیچ پزشکی انتخاب نشده است. لطفاً ابتدا یک پزشک انتخاب کنید.');
            }

            $doctor = \App\Models\Doctor::find($selectedDoctorId);
            if (!$doctor) {
                throw new \Exception('پزشک انتخاب‌شده یافت نشد.');
            }

            return $doctor;
        }
        throw new \Exception('کاربر احراز هویت نشده است.');
    }

    public function index(Request $request)
    {
        $doctor = $this->getAuthenticatedDoctor();
        if (!$doctor) {
            abort(403, 'شما به این بخش دسترسی ندارید.');
        }

        $medicalCenter = Auth::guard('medical_center')->user();
        $medicalCenterId = $medicalCenter->id;
        $filterType = $request->input('type');
        $now = Carbon::now()->format('Y-m-d');

        $appointments = Appointment::with(['doctor', 'patientable', 'insurance', 'medicalCenter'])
            ->where('doctor_id', $doctor->id)
            ->where('medical_center_id', $medicalCenterId)
            ->where('appointment_date', $now);

        if (empty($filterType)) {
            $appointments->withTrashed();
        }

        if ($filterType) {
            if ($filterType === "in_person") {
                $appointments->where('medical_center_id', $medicalCenterId);
            } elseif ($filterType === "online") {
                $appointments->whereNull('medical_center_id');
            }
        }

        $appointments = $appointments->get();

        return view('mc.panel.turn.schedule.appointments', compact('appointments'));
    }

    public function getAppointmentsByDate(Request $request)
    {
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

        $doctor = $this->getAuthenticatedDoctor();
        $medicalCenter = Auth::guard('medical_center')->user();

        $query = Appointment::where('doctor_id', $doctor->id)
            ->where('medical_center_id', $medicalCenter->id)
            ->whereDate('appointment_date', $gregorianDate)
            ->with(['patientable', 'insurance', 'medicalCenter']);

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

        $doctor = $this->getAuthenticatedDoctor();
        $medicalCenter = Auth::guard('medical_center')->user();

        $appointmentsQuery = Appointment::with('patientable', 'insurance', 'medicalCenter')
            ->where('doctor_id', $doctor->id)
            ->where('medical_center_id', $medicalCenter->id)
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

        $medicalCenter = Auth::guard('medical_center')->user();
        $medicalCenterId = $medicalCenter->id;

        $query = Appointment::withTrashed()
            ->where('medical_center_id', $medicalCenterId);

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($attendanceStatus)) {
            $query->where('attendance_status', $attendanceStatus);
        }

        $appointments = $query->with(['patientable', 'doctor', 'medicalCenter', 'insurance'])->paginate(10);

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
            $doctor = $this->getAuthenticatedDoctor();
            $medicalCenter = Auth::guard('medical_center')->user();

            $appointment = Appointment::where('id', $id)
                ->where('doctor_id', $doctor->id)
                ->where('medical_center_id', $medicalCenter->id)
                ->firstOrFail();

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

        $medicalCenter = Auth::guard('medical_center')->user();
        $medicalCenterId = $medicalCenter->id;

        $subUserIds = SubUser::where('owner_id', $doctor->id)
            ->where('owner_type', \App\Models\Doctor::class)
            ->where('subuserable_type', \App\Models\User::class)
            ->pluck('subuserable_id')
            ->toArray();

        $appointments = Appointment::with(['patientable', 'medicalCenter'])
            ->whereIn('patientable_id', $subUserIds)
            ->where('patientable_type', User::class)
            ->where('medical_center_id', $medicalCenterId)
            ->orderBy('appointment_date', 'desc')
            ->paginate(10);

        return view("mc.panel.turn.schedule.my-appointments", compact('appointments'));
    }

    public function getAppointmentsCount($doctorId, $date)
    {
        try {
            $medicalCenter = Auth::guard('medical_center')->user();
            if (!$medicalCenter) {
                return response()->json([
                    'success' => false,
                    'message' => 'کاربر احراز هویت نشده است.'
                ], 401);
            }

            // بررسی اینکه آیا پزشک انتخاب‌شده همان پزشک درخواستی است
            $selectedDoctorId = $medicalCenter->selectedDoctor?->doctor_id;
            if ($selectedDoctorId != $doctorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'دسترسی غیرمجاز به اطلاعات پزشک دیگر.'
                ], 403);
            }

            // شمارش نوبت‌ها برای تاریخ مشخص
            $count = Appointment::where('doctor_id', $doctorId)
                ->where('medical_center_id', $medicalCenter->id)
                ->whereDate('appointment_date', $date)
                ->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت تعداد نوبت‌ها: ' . $e->getMessage()
            ], 500);
        }
    }
}
