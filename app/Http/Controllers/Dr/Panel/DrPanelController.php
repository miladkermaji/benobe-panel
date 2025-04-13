<?php

namespace App\Http\Controllers\Dr\Panel;

use Carbon\Carbon;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Morilog\Jalali\Jalalian;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Dr\Controller;

class DrPanelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $today    = Carbon::today();
        $doctorId = Auth::guard('doctor')->user()->id ?? Auth::guard('secretary')->user()->doctor_id; // گرفتن ID پزشک لاگین‌شده
        // تعداد بیماران امروز فقط برای این پزشک
        $totalPatientsToday = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->count();
        // بیماران ویزیت شده فقط برای این پزشک
        $visitedPatients = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $today)
            ->where('attendance_status', 'attended')
            ->count();
        // بیماران باقی‌مانده فقط برای این پزشک
        $remainingPatients = $totalPatientsToday - $visitedPatients;
        return view("dr.panel.index", compact('totalPatientsToday', 'visitedPatients', 'remainingPatients'));
    }
    public function getAppointmentsByDate(Request $request)
    {
        $selectedClinicId = $request->selectedClinicId;
        $jalaliDate = $request->input('date'); // دریافت تاریخ از فرانت‌اند

        // بررسی فرمت تاریخ ورودی
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $jalaliDate)) {
            $gregorianDate = $jalaliDate;
        } else {
            if (strpos($jalaliDate, '-') !== false) {
                $jalaliDate = str_replace('-', '/', $jalaliDate);
            }

            if (!preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $jalaliDate)) {
                return response()->json(['error' => 'فرمت تاریخ جلالی نادرست است.'], 400);
            }

            try {
                $gregorianDate = Jalalian::fromFormat('Y/m/d', $jalaliDate)->toCarbon()->format('Y-m-d');
            } catch (\Exception $e) {
                return response()->json(['error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'], 500);
            }
        }

        $doctorId = Auth::guard('doctor')->user()->id;

        // کوئری پایه برای دریافت نوبت‌ها
        $query = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $gregorianDate)
            ->with(['patient', 'insurance']);

        // اعمال فیلتر selectedClinicId
        if ($selectedClinicId === 'default') {
            $query->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            $query->where('clinic_id', $selectedClinicId);
        }

        // افزودن پیجینیشن (مثلاً 10 نوبت در هر صفحه)
        $appointments = $query->paginate(10);

        return response()->json([
            'appointments' => $appointments->items(), // داده‌های نوبت‌ها
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
            return response()->json(['error' => 'تاریخ جستجو الزامی است.'], 400);
        }

        if (strpos($date, '-') !== false) {
            $date = str_replace('-', '/', $date);
        }

        if (!preg_match('/^\d{4}\/\d{2}\/\d{2}$/', $date)) {
            return response()->json(['error' => 'فرمت تاریخ جلالی نادرست است.'], 400);
        }

        try {
            $gregorianDate = Jalalian::fromFormat('Y/m/d', $date)->toCarbon()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'خطا در تبدیل تاریخ جلالی به میلادی.'], 500);
        }

        $appointmentsQuery = Appointment::with('patient', 'insurance')
            ->whereDate('appointment_date', $gregorianDate)
            ->whereHas('patient', function ($q) use ($query) {
                $q->where('first_name', 'like', "%$query%")
                    ->orWhere('last_name', 'like', "%$query%")
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%$query%"])
                    ->orWhere('mobile', 'like', "%$query%")
                    ->orWhere('national_code', 'like', "%$query%");
            });

        if ($selectedClinicId === 'default') {
            $appointmentsQuery->whereNull('clinic_id');
        } elseif ($selectedClinicId) {
            $appointmentsQuery->where('clinic_id', $selectedClinicId);
        }

        // افزودن پیجینیشن
        $patients = $appointmentsQuery->paginate(10);

        return response()->json([
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

        // چک کردن وضعیت نوبت
        if ($appointment->status === 'attended' || $appointment->status === 'cancelled') {
            return response()->json(['error' => 'نمی‌توانید نوبت ویزیت‌شده یا لغو شده را جابجا کنید.'], 400);
        }

        $newDate = Carbon::parse($request->new_date);
        if ($newDate->lt(Carbon::today())) {
            return response()->json(['error' => 'امکان جابجایی به تاریخ گذشته وجود ندارد.'], 400);
        }

        $oldDate = $appointment->appointment_date; // تاریخ قبلی
        $appointment->appointment_date = $newDate;
        $appointment->save();

        // تبدیل تاریخ‌ها به فرمت شمسی
        $oldDateJalali = Jalalian::fromDateTime($oldDate)->format('Y/m/d');
        $newDateJalali = Jalalian::fromDateTime($newDate)->format('Y/m/d');

        // ارسال پیامک جابجایی نوبت
        if ($appointment->patient && $appointment->patient->mobile) {
            $message = "کاربر گرامی، نوبت شما از تاریخ {$oldDateJalali} به {$newDateJalali} تغییر یافت.";
            SendSmsNotificationJob::dispatch(
                $message,
                [$appointment->patient->mobile],
                null, // بدون قالب
                [] // بدون پارامتر اضافی
            )->delay(now()->addSeconds(5));
        }

        return response()->json(['message' => 'نوبت با موفقیت جابجا شد .']);
    }
    public function filterAppointments(Request $request)
    {
        $status = $request->query('status');
        $attendanceStatus = $request->query('attendance_status');
        $selectedClinicId = $request->input('selectedClinicId');

        $query = Appointment::withTrashed();

        if ($selectedClinicId && $selectedClinicId !== 'default') {
            $query->where('clinic_id', $selectedClinicId);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($attendanceStatus)) {
            $query->where('attendance_status', $attendanceStatus);
        }

        // افزودن پیجینیشن
        $appointments = $query->with(['patient', 'doctor', 'clinic', 'insurance'])->paginate(10);

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
            // پیدا کردن نوبت با شناسه
            $appointment = Appointment::findOrFail($id);

            // اعتبارسنجی درخواست
            $request->validate([
                'description' => 'nullable|string|max:1000', // توضیحات اختیاری
            ]);

            // به‌روزرسانی توضیحات و وضعیت نوبت
            $appointment->description = $request->input('description');
            $appointment->status = 'attended'; // تغییر وضعیت به attended
            $appointment->attendance_status = 'attended'; // هماهنگ کردن attendance_status
            $appointment->save();

            // برگرداندن پاسخ موفقیت‌آمیز
            return response()->json([
                'success' => true,
                'message' => 'ویزیت با موفقیت ثبت شد.',
                'appointment' => $appointment // اطلاعات نوبت به‌روز شده
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در ثبت ویزیت: ' . $e->getMessage()
            ], 500);
        }
    }
}
